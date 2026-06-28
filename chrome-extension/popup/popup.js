/**
 * OfferGuard Chrome Extension - Popup Logic
 * 处理弹窗的所有业务逻辑
 */

(function() {
    'use strict';

    // API基础地址
    const API_BASE_URL = 'http://127.0.0.1:8000/api';

    // DOM元素
    const elements = {
        productImage: document.getElementById('product-image'),
        imagePlaceholder: document.getElementById('image-placeholder'),
        productTitle: document.getElementById('product-title'),
        productOfferId: document.getElementById('product-offer-id'),
        productPrice: document.getElementById('product-price'),
        statusBadge: document.getElementById('status-badge'),
        statusText: document.getElementById('status-text'),
        resultSection: document.getElementById('result-section'),
        result1688: document.getElementById('result-1688'),
        resultTrademark: document.getElementById('result-trademark'),
        resultReason: document.getElementById('result-reason'),
        detectBtn: document.getElementById('detect-btn'),
        monitorBtn: document.getElementById('monitor-btn'),
        settingsToggle: document.getElementById('settings-toggle'),
        settingsPanel: document.getElementById('settings-panel'),
        apiTokenInput: document.getElementById('api-token'),
        saveTokenBtn: document.getElementById('save-token-btn'),
        deviceIdDisplay: document.getElementById('device-id-display'),
        clearTokenBtn: document.getElementById('clear-token-btn')
    };

    // 当前商品数据
    let currentProduct = null;

    // 当前检测结果
    let currentResult = null;

    /**
     * 生成随机UUID
     * @returns {string} - UUID字符串
     */
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * 获取或创建DeviceId
     * @returns {Promise<string>} - DeviceId
     */
    async function getDeviceId() {
        let deviceId = localStorage.getItem('offerguard_device_id');
        
        if (!deviceId) {
            deviceId = generateUUID();
            localStorage.setItem('offerguard_device_id', deviceId);
        }
        
        return deviceId;
    }

    /**
     * 获取API Token
     * @returns {Promise<string|null>} - Token
     */
    async function getToken() {
        return new Promise((resolve) => {
            chrome.storage.local.get('offerguard_token', (result) => {
                resolve(result.offerguard_token || null);
            });
        });
    }

    /**
     * 保存API Token
     * @param {string} token - Token值
     */
    function saveToken(token) {
        chrome.storage.local.set({ 'offerguard_token': token });
    }

    /**
     * 清除API Token
     */
    function clearToken() {
        chrome.storage.local.remove('offerguard_token');
        elements.apiTokenInput.value = '';
        updateStatus();
    }

    /**
     * 发送API请求
     * @param {string} endpoint - API端点
     * @param {Object} data - 请求数据
     * @returns {Promise<Object>} - 响应数据
     */
    async function apiRequest(endpoint, data = {}) {
        const deviceId = await getDeviceId();
        const token = await getToken();

        const headers = {
            'Content-Type': 'application/json',
            'X-Device-Id': deviceId
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(data)
        });

        return await response.json();
    }

    /**
     * 获取配额状态
     * @returns {Promise<Object>} - 配额信息
     */
    async function getQuotaStatus() {
        const deviceId = await getDeviceId();
        const token = await getToken();

        const headers = {
            'X-Device-Id': deviceId
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(`${API_BASE_URL}/quota/status`, {
            method: 'GET',
            headers: headers
        });

        return await response.json();
    }

    /**
     * 更新状态显示
     */
    async function updateStatus() {
        try {
            const result = await getQuotaStatus();
            
            if (result.success) {
                if (result.is_logged_in) {
                    elements.statusText.textContent = '已登录 - 无限次检测';
                    elements.statusBadge.style.background = '#ECFDF5';
                    elements.statusBadge.style.color = '#10B981';
                    elements.statusBadge.querySelector('.status-icon').style.background = '#10B981';
                } else {
                    const remaining = result.quota_remaining || 0;
                    elements.statusText.textContent = `免费检测: ${remaining}次`;
                    elements.statusBadge.style.background = '#EFF6FF';
                    elements.statusBadge.style.color = '#3B82F6';
                    elements.statusBadge.querySelector('.status-icon').style.background = '#3B82F6';
                }
            }
        } catch (error) {
            console.error('Failed to get quota status:', error);
            elements.statusText.textContent = '检测配额: 获取中...';
        }
    }

    /**
     * 从内容脚本获取商品信息
     * @returns {Promise<Object>} - 商品信息
     */
    function fetchProductInfo() {
        return new Promise((resolve) => {
            chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
                if (tabs.length === 0) {
                    resolve({ success: false, message: '无法获取当前标签页' });
                    return;
                }

                chrome.tabs.sendMessage(tabs[0].id, { action: 'getProductInfo' }, (response) => {
                    if (chrome.runtime.lastError) {
                        resolve({ success: false, message: '内容脚本未加载' });
                    } else {
                        resolve(response || { success: false, message: '无响应' });
                    }
                });
            });
        });
    }

    /**
     * 渲染商品信息
     * @param {Object} product - 商品数据
     */
    function renderProduct(product) {
        currentProduct = product;

        // 图片
        if (product.mainImage) {
            elements.productImage.src = product.mainImage;
            elements.productImage.classList.remove('hidden');
            elements.imagePlaceholder.classList.add('hidden');
        } else {
            elements.productImage.classList.add('hidden');
            elements.imagePlaceholder.classList.remove('hidden');
        }

        // 标题
        elements.productTitle.textContent = product.title || '未知标题';

        // Offer ID
        elements.productOfferId.textContent = product.offerId || '-';

        // 价格
        if (product.price) {
            elements.productPrice.textContent = `¥${product.price}`;
        } else {
            elements.productPrice.textContent = '-';
        }
    }

    /**
     * 显示加载状态
     */
    function showLoading() {
        elements.detectBtn.disabled = true;
        elements.detectBtn.innerHTML = `
            <span class="loading-dots">
                <span></span><span></span><span></span>
            </span>
            <span class="btn-text">检测中...</span>
        `;
        
        elements.resultSection.classList.remove('hidden');
        elements.result1688.innerHTML = '<span class="loading-dots"><span></span><span></span><span></span></span>';
        elements.resultTrademark.innerHTML = '<span class="loading-dots"><span></span><span></span><span></span></span>';
        elements.resultReason.textContent = '';
        elements.monitorBtn.classList.add('hidden');
    }

    /**
     * 隐藏加载状态
     */
    function hideLoading() {
        elements.detectBtn.disabled = false;
        elements.detectBtn.innerHTML = `
            <span class="btn-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                    <path d="M16 21h5v-5"/>
                </svg>
            </span>
            <span class="btn-text">立即检测风险</span>
        `;
    }

    /**
     * 渲染检测结果
     * @param {Object} result - 检测结果
     */
    function renderResult(result) {
        currentResult = result;

        // 1688状态
        const check1688 = result.check_1688;
        if (check1688.status === 'online') {
            elements.result1688.textContent = '在线';
            elements.result1688.className = 'result-value success';
        } else {
            elements.result1688.textContent = '已下架';
            elements.result1688.className = 'result-value danger';
        }

        // 商标风险
        const checkTrademark = result.check_trademark;
        if (checkTrademark.status === 'high') {
            elements.resultTrademark.textContent = '高风险';
            elements.resultTrademark.className = 'result-value warning';
        } else {
            elements.resultTrademark.textContent = '低风险';
            elements.resultTrademark.className = 'result-value info';
        }

        // 原因
        const reasons = [];
        if (check1688.reason) reasons.push(check1688.reason);
        if (checkTrademark.reason) reasons.push(checkTrademark.reason);
        elements.resultReason.textContent = reasons.join(' | ');

        // 更新配额显示
        updateStatus();

        // 显示"加入监控"按钮（仅登录且付费用户）
        setTimeout(async () => {
            const quota = await getQuotaStatus();
            if (quota.success && quota.is_logged_in && quota.is_paid) {
                elements.monitorBtn.classList.remove('hidden');
            }
        }, 500);
    }

    /**
     * 处理检测按钮点击
     */
    async function handleDetect() {
        if (!currentProduct || !currentProduct.success) {
            alert('请先在1688商品详情页打开插件');
            return;
        }

        showLoading();

        try {
            const data = {
                offer_id: currentProduct.offerId,
                title: currentProduct.title,
                price: currentProduct.price,
                main_image: currentProduct.mainImage,
                source_url: currentProduct.sourceUrl
            };

            const result = await apiRequest('/detect', data);

            if (result.success) {
                renderResult(result.data);
            } else {
                alert(result.message || '检测失败');
                elements.resultSection.classList.add('hidden');
            }
        } catch (error) {
            console.error('Detection failed:', error);
            alert('检测失败，请稍后重试');
            elements.resultSection.classList.add('hidden');
        } finally {
            hideLoading();
        }
    }

    /**
     * 处理加入监控按钮点击
     */
    async function handleMonitor() {
        if (!currentProduct || !currentProduct.success) {
            alert('请先获取商品信息');
            return;
        }

        elements.monitorBtn.disabled = true;
        elements.monitorBtn.innerHTML = `
            <span class="loading-dots">
                <span></span><span></span><span></span>
            </span>
            <span class="btn-text">添加中...</span>
        `;

        try {
            const data = {
                offer_id: currentProduct.offerId,
                title: currentProduct.title,
                price: currentProduct.price,
                main_image: currentProduct.mainImage,
                source_url: currentProduct.sourceUrl
            };

            const result = await apiRequest('/monitor/add', data);

            if (result.success) {
                alert('已成功加入监控库！');
                elements.monitorBtn.textContent = '已加入监控';
                elements.monitorBtn.disabled = true;
            } else {
                alert(result.message || '添加失败');
                elements.monitorBtn.disabled = false;
                elements.monitorBtn.innerHTML = `
                    <span class="btn-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                    </span>
                    <span class="btn-text">加入持续监控</span>
                `;
            }
        } catch (error) {
            console.error('Add monitor failed:', error);
            alert('添加失败，请稍后重试');
            elements.monitorBtn.disabled = false;
            elements.monitorBtn.innerHTML = `
                <span class="btn-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </span>
                <span class="btn-text">加入持续监控</span>
            `;
        }
    }

    /**
     * 初始化设置面板
     */
    async function initSettings() {
        const deviceId = await getDeviceId();
        elements.deviceIdDisplay.textContent = deviceId;

        const token = await getToken();
        elements.apiTokenInput.value = token || '';
    }

    /**
     * 绑定事件
     */
    function bindEvents() {
        // 检测按钮
        elements.detectBtn.addEventListener('click', handleDetect);

        // 加入监控按钮
        elements.monitorBtn.addEventListener('click', handleMonitor);

        // 设置面板切换
        elements.settingsToggle.addEventListener('click', () => {
            elements.settingsPanel.classList.toggle('hidden');
        });

        // 保存Token
        elements.saveTokenBtn.addEventListener('click', () => {
            const token = elements.apiTokenInput.value.trim();
            saveToken(token);
            updateStatus();
            alert('Token已保存');
        });

        // 清除Token
        elements.clearTokenBtn.addEventListener('click', () => {
            if (confirm('确定要清除Token吗？')) {
                clearToken();
            }
        });
    }

    /**
     * 初始化插件
     */
    async function init() {
        // 获取商品信息
        const product = await fetchProductInfo();
        
        if (product.success) {
            renderProduct(product);
        } else {
            elements.productTitle.textContent = product.message || '无法提取商品信息';
            elements.productTitle.style.color = '#EF4444';
            elements.detectBtn.disabled = true;
        }

        // 更新配额状态
        await updateStatus();

        // 初始化设置
        await initSettings();

        // 绑定事件
        bindEvents();
    }

    // 启动
    document.addEventListener('DOMContentLoaded', init);
})();
