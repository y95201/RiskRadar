<?php

namespace App\Services;

/**
 * 商标侵权检测服务
 * 提取商品标题中的英文单词，进行侵权初筛
 */
class CheckTrademarkService
{
    /**
     * 常见商标黑名单（示例）
     * 实际使用中应从数据库或外部API获取
     */
    protected const TRADEMARK_BLACKLIST = [
        'nike',
        'adidas',
        'apple',
        'samsung',
        'sony',
        'gucci',
        'lv',
        'chanel',
        'dior',
        'louis vuitton',
        'hermes',
        'prada',
        'armani',
        'boss',
        'puma',
        'reebok',
        'converse',
        'vans',
        'new balance',
        'under armour',
        'columbia',
        'north face',
        'patagonia',
        'coca cola',
        'pepsi',
        'mcdonalds',
        'kfc',
        'starbucks',
        'disney',
        'mickey',
        'hello kitty',
        'barbie',
        'lego',
        'sony',
        'panasonic',
        'toshiba',
        'hitachi',
        'sharp',
        'philips',
        'siemens',
        'bosch',
        'lg',
        'huawei',
        'xiaomi',
        'oppo',
        'vivo',
        'meizu',
        'oneplus',
        'realme',
    ];

    /**
     * 检测商品标题中的商标风险
     *
     * @param string $title 商品标题
     * @return array ['status' => 'high'|'low', 'reason' => string]
     */
    public function check(string $title): array
    {
        // 提取标题中的英文单词
        $englishWords = $this->extractEnglishWords($title);

        if (empty($englishWords)) {
            return [
                'status' => 'low',
                'reason' => '标题中未检测到英文单词',
            ];
        }

        // 检测是否包含黑名单中的商标
        $matchedTrademarks = $this->matchTrademarks($englishWords);

        if (!empty($matchedTrademarks)) {
            return [
                'status' => 'high',
                'reason' => '检测到疑似侵权商标: ' . implode(', ', $matchedTrademarks),
            ];
        }

        return [
            'status' => 'low',
            'reason' => '未检测到已知侵权商标',
        ];
    }

    /**
     * 从标题中提取英文单词
     *
     * @param string $title 商品标题
     * @return array 英文单词数组
     */
    protected function extractEnglishWords(string $title): array
    {
        // 使用正则表达式匹配英文单词（至少2个字母）
        preg_match_all('/[a-zA-Z]{2,}/', $title, $matches);

        if (empty($matches[0])) {
            return [];
        }

        // 转换为小写并去重
        $words = array_unique(array_map('strtolower', $matches[0]));

        return array_values($words);
    }

    /**
     * 匹配黑名单中的商标
     *
     * @param array $words 英文单词数组
     * @return array 匹配到的商标数组
     */
    protected function matchTrademarks(array $words): array
    {
        $matched = [];

        foreach (self::TRADEMARK_BLACKLIST as $trademark) {
            $trademarkLower = strtolower($trademark);
            
            // 检查单个单词是否匹配
            if (in_array($trademarkLower, $words)) {
                $matched[] = $trademark;
                continue;
            }

            // 检查多词商标（如 "louis vuitton"）
            $parts = explode(' ', $trademarkLower);
            if (count($parts) > 1) {
                $allPartsFound = true;
                foreach ($parts as $part) {
                    if (!in_array($part, $words)) {
                        $allPartsFound = false;
                        break;
                    }
                }
                if ($allPartsFound) {
                    $matched[] = $trademark;
                }
            }
        }

        return $matched;
    }
}