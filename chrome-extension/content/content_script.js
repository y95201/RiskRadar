/**
 * OfferGuard Content Script
 * 在1688商品详情页注入，提取商品信息
 */

(function() {
    'use strict';

    /**
     * 从URL中提取offerId
     * @param {string} url - 当前页面URL
     * @returns {string|null} - offerId
     */
    function extractOfferId(url) {
        const match = url.match(/offer\/(\d+)\.html/);
        return match ? match[1] : null;
    }

    /**
     * 从页面脚本中提取商品数据
     * 通过正则匹配<script>标签中的JSON数据
     * @returns {Object|null} - 商品数据对象
     */
    function extractProductData() {
        const scripts = document.querySelectorAll('script');
        let productData = null;

        for (const script of scripts) {
            const content = script.textContent || '';
            
            if (!content.includes('subject') && !content.includes('price')) {
                continue;
            }

            // 提取标题: "subject":"商品标题"
            const titleMatch = content.match(/"subject":"([^"]+)"/);
            const title = titleMatch ? titleMatch[1] : '';

            // 提取价格: "price":"123.45"
            const priceMatch = content.match(/"price":"(\d+\.?\d*)"/);
            const price = priceMatch ? priceMatch[1] : '';

            // 提取主图: "mainImage":["//xxx.jpg"]
            const imageMatch = content.match(/"mainImage":\["([^"]+)"/);
            let mainImage = '';
            if (imageMatch) {
                mainImage = imageMatch[1];
                // 如果是//开头，补全为https:
                if (mainImage.startsWith('//')) {
                    mainImage = 'https:' + mainImage;
                }
            }

            if (title || price || mainImage) {
                productData = {
                    title: title,
                    price: price,
                    mainImage: mainImage,
                    sourceUrl: window.location.href
                };
                break;
            }
        }

        return productData;
    }

    /**
     * 获取商品信息
     * @returns {Object} - 完整的商品信息
     */
    function getProductInfo() {
        const offerId = extractOfferId(window.location.href);
        
        if (!offerId) {
            return {
                success: false,
                message: '无法提取offerId'
            };
        }

        const productData = extractProductData();

        if (!productData) {
            return {
                success: false,
                message: '无法提取商品信息'
            };
        }

        return {
            success: true,
            offerId: offerId,
            title: productData.title,
            price: productData.price,
            mainImage: productData.mainImage,
            sourceUrl: productData.sourceUrl
        };
    }

    /**
     * 监听来自Popup的消息
     */
    chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
        console.log('Content script received message:', request);

        if (request.action === 'getProductInfo') {
            const result = getProductInfo();
            console.log('Extracted product info:', result);
            sendResponse(result);
        }

        return true;
    });

    console.log('OfferGuard content script loaded');
})();
