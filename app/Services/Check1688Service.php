<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * 1688商品状态检测服务
 * 通过请求1688商品详情页，判断商品是否下架
 */
class Check1688Service
{
    /**
     * Guzzle HTTP客户端
     *
     * @var Client
     */
    protected $client;

    /**
     * 1688商品详情页URL模板
     */
    protected const URL_TEMPLATE = 'https://detail.1688.com/offer/%s.html';

    /**
     * 商品不存在/已下架的关键词列表
     */
    protected const OFFLINE_KEYWORDS = [
        '商品不存在',
        '已下架',
        '该商品已下架',
        '商品已删除',
        '无法访问',
        '404',
        '抱歉，您访问的页面不存在',
    ];

    /**
     * 构造函数
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * 检测1688商品状态
     *
     * @param string $offerId 商品ID
     * @return array ['status' => 'online'|'offline', 'reason' => string]
     */
    public function check(string $offerId): array
    {
        try {
            // 构建请求URL
            $url = sprintf(self::URL_TEMPLATE, $offerId);
            
            // 发送HTTP请求，添加模拟浏览器的Header
            $response = $this->client->get($url, [
                'headers' => $this->getDefaultHeaders(),
                'timeout' => 30,
                'verify' => false, // 禁用SSL验证，避免证书问题
            ]);

            // 获取页面内容
            $content = (string) $response->getBody();

            // 判断商品状态
            if ($this->isOffline($content)) {
                return [
                    'status' => 'offline',
                    'reason' => '检测到商品已下架或不存在',
                ];
            }

            return [
                'status' => 'online',
                'reason' => '商品正常在售',
            ];

        } catch (GuzzleException $e) {
            // 请求异常，视为离线状态
            return [
                'status' => 'offline',
                'reason' => '请求1688失败: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * 判断页面内容是否包含下架关键词
     *
     * @param string $content 页面内容
     * @return bool
     */
    protected function isOffline(string $content): bool
    {
        foreach (self::OFFLINE_KEYWORDS as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取默认的HTTP请求头
     * 模拟浏览器请求，减少被拦截的概率
     *
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Cache-Control' => 'max-age=0',
        ];
    }
}