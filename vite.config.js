import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

import http from 'http';

export default defineConfig({
    plugins: [
        {
            name: 'vite-dev-proxy-to-laravel',
            configureServer(server) {
                server.middlewares.use((req, res, next) => {
                    const url = req.url || '';
                    const isAsset = url.startsWith('/@') ||
                                    url.startsWith('/resources/') ||
                                    url.startsWith('/node_modules/') ||
                                    url.startsWith('/__vite') ||
                                    url.match(/\.(js|css|ts|tsx|jsx|json|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)(\?.*)?$/i);
                    if (isAsset) {
                        return next();
                    }

                    const proxyReq = http.request({
                        host: '127.0.0.1',
                        port: 8000,
                        path: req.url,
                        method: req.method,
                        headers: {
                            ...req.headers,
                            host: '127.0.0.1:8000',
                            'x-forwarded-host': req.headers.host || '127.0.0.1:5173',
                            'x-forwarded-proto': 'http',
                        },
                    }, (proxyRes) => {
                        res.writeHead(proxyRes.statusCode || 200, proxyRes.headers);
                        proxyRes.pipe(res);
                    });

                    proxyReq.on('error', () => {
                        res.writeHead(503, { 'Content-Type': 'text/html; charset=utf-8' });
                        res.end(`<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TailorPro - Connecting Backend...</title><meta http-equiv="refresh" content="2"></head><body style="margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0f172a;color:#f8fafc;font-family:sans-serif;text-align:center;padding:20px;"><div style="max-width:440px;background:#1e293b;border:1px solid #334155;border-radius:16px;padding:32px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);"><div style="width:48px;height:48px;border:3px solid rgba(59,130,246,0.3);border-top-color:#3b82f6;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 16px auto;"></div><h2 style="font-size:18px;margin:0 0 8px 0;font-weight:600;">Starting TailorPro Services</h2><p style="font-size:13px;color:#94a3b8;line-height:1.5;margin:0 0 16px 0;">Connecting to the application server at http://127.0.0.1:8000. Re-connecting automatically...</p><a href="http://127.0.0.1:8000" style="display:inline-block;padding:10px 20px;border-radius:8px;background:#2563eb;color:#ffffff;text-decoration:none;font-size:13px;font-weight:500;">Open Direct Link (Port 8000)</a></div><style>@keyframes spin{to{transform:rotate(360deg)}}</style></body></html>`);
                    });

                    req.pipe(proxyReq);
                });
            },
        },
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    css: {
        postcss: {},
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        chunkSizeWarningLimit: 1200,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('jspdf') || id.includes('html2canvas') || id.includes('dompurify')) {
                            return 'vendor-pdf';
                        }
                        if (id.includes('lucide-vue-next')) {
                            return 'vendor-icons';
                        }
                        if (id.includes('vue') || id.includes('pinia') || id.includes('axios')) {
                            return 'vendor-core';
                        }
                    }
                },
            },
        },
    },
});
