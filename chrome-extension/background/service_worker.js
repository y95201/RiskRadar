/**
 * OfferGuard Chrome Extension - Service Worker
 * 处理后台任务和事件监听
 */

self.addEventListener('install', () => {
    console.log('OfferGuard extension installed');
});

self.addEventListener('activate', () => {
    console.log('OfferGuard extension activated');
});

self.addEventListener('message', (event) => {
    console.log('Service worker received message:', event.data);
});
