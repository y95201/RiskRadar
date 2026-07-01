<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Agnes Video 生成器</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #0b0e14;
            --surface: #151e2a;
            --surface2: #1f2a3a;
            --border: #2d3a4e;
            --text: #eaf0f6;
            --text2: #a0b3cc;
            --accent: #5b8cff;
            --accent-hover: #7aa2ff;
            --radius: 12px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
        }

        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .app {
            max-width: 1000px;
            width: 100%;
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        h1 i {
            color: var(--accent);
        }
        .sub {
            color: var(--text2);
            font-size: 15px;
            margin-bottom: 30px;
            border-left: 3px solid var(--accent);
            padding-left: 16px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
        }

        .card h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card h2 i {
            color: var(--accent);
            width: 24px;
            text-align: center;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px 24px;
            margin-bottom: 16px;
        }

        .field {
            flex: 1 1 200px;
            min-width: 160px;
        }
        .field.full {
            flex: 1 1 100%;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text2);
            margin-bottom: 5px;
        }
        label .required {
            color: #ff6b7c;
            margin-left: 4px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            transition: border 0.2s;
            font-family: inherit;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(91, 140, 255, 0.15);
        }
        textarea {
            resize: vertical;
            min-height: 70px;
        }
        input[type="file"] {
            padding: 8px;
            background: var(--surface2);
        }

        .file-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }
        .file-preview .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: var(--bg);
        }
        .file-preview .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .file-preview .preview-item .remove {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .file-preview .preview-item .remove:hover {
            background: #ff4d6a;
        }
        .file-preview .preview-item .preview-order {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.78);
            color: #fff;
            font-size: 11px;
            text-align: center;
            padding: 2px 0;
            font-weight: 600;
        }
        .file-preview .preview-item .reorder-btn {
            position: absolute;
            top: 4px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .file-preview .preview-item .reorder-left {
            left: 4px;
        }
        .file-preview .preview-item .reorder-right {
            left: 30px;
        }
        .file-preview .preview-item .reorder-btn:hover {
            background: var(--accent);
        }

        .btn {
            background: var(--accent);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn:hover {
            background: var(--accent-hover);
        }
        .btn:active {
            transform: scale(0.97);
        }
        .btn:disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .btn-secondary {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text2);
        }
        .btn-secondary:hover {
            background: var(--border);
            color: #fff;
        }
        .duration-btn.active {
            background: #2a9dff !important;
            border-color: #2a9dff !important;
            color: #fff !important;
        }
        .btn-info {
            background: #4a9eff;
            border-color: #4a9eff;
            color: #fff;
        }
        .btn-info:hover {
            background: #6db5ff;
        }
        .btn-danger {
            background: #ff6b4a;
            border-color: #ff6b4a;
            color: #fff;
        }
        .btn-danger:hover {
            background: #ff8a6f;
        }
        .template-btn {
            padding: 6px 12px;
            font-size: 12px;
        }
        .duration-btn {
            padding: 8px 16px;
            font-size: 14px;
        }
        .info-banner {
            background: rgba(42, 157, 255, 0.15);
            padding: 12px;
            border-radius: 8px;
        }
        .info-banner-title {
            font-weight: 600;
            margin-bottom: 6px;
        }
        .info-banner-body {
            font-size: 13px;
        }

        .status-bar {
            margin-top: 16px;
            padding: 14px 18px;
            background: var(--bg);
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .status-bar .label {
            font-weight: 500;
            color: var(--text2);
        }
        .status-bar .value {
            font-family: monospace;
            color: var(--accent);
        }
        .progress-track {
            flex: 1;
            min-width: 120px;
            height: 6px;
            background: var(--border);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-track .fill {
            height: 100%;
            width: 0%;
            background: var(--accent);
            transition: width 0.5s ease;
        }

        .result-video {
            margin-top: 16px;
            border-radius: var(--radius);
            overflow: hidden;
            background: #000;
            max-width: 100%;
        }
        .result-video video {
            width: 100%;
            display: block;
        }

        .error-msg {
            color: #ff6b7c;
            background: rgba(255, 107, 124, 0.1);
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #ff6b7c;
            margin-top: 12px;
            font-size: 14px;
        }

        .hint {
            font-size: 13px;
            color: var(--text2);
            margin-top: 4px;
        }

        .flex-center {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 600px) {
            .card {
                padding: 16px;
            }
            .field {
                flex: 1 1 100%;
            }
            .row {
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    <div class="app">
        <header>
            <h1><i class="fas fa-video"></i> Agnes Video 生成器</h1>
            <div class="sub">
                <i class="fas fa-cloud-upload-alt" style="margin-right:8px;"></i>
                上传图片 + 输入提示词，调用 Agnes-Video-V2.0 生成视频
                <span style="display:inline-block;margin-left:16px;font-size:13px;color:var(--text2);">
                    <i class="fas fa-info-circle"></i> 图片上传到服务器后生成 URL
                </span>
            </div>
        </header>

        <form id="generationForm" class="card" novalidate>
            <h2><i class="fas fa-sliders-h"></i> 参数设置</h2>

            <!-- 模式 -->
            <div class="row">
                <div class="field">
                    <label>生成模式 <span class="required">*</span></label>
                    <select id="mode">
                        <option value="t2v">文生视频 (Text-to-Video)</option>
                        <option value="i2v" selected>图生视频 (Image-to-Video)</option>
                        <option value="multi">多图视频 (Multi-Image)</option>
                        <option value="keyframes">关键帧动画 (Keyframes)</option>
                    </select>
                </div>
                <div class="field">
                    <label>模型</label>
                    <input id="model" value="agnes-video-v2.0" disabled style="opacity:0.7;" />
                </div>
            </div>

            <!-- 图片上传 -->
            <div id="imageUploadArea" class="row">
                <div class="field full">
                    <label id="imageLabel">上传图片 (图生视频) <span class="required">*</span></label>
                    <input type="file" id="imageInput" accept="image/*" multiple />
                    <div class="hint" id="imageHint">支持 JPG, PNG, WEBP。多图模式请选择多张图片</div>
                    <div class="file-preview" id="previewContainer"></div>
                </div>
            </div>

            <!-- Prompt -->
            <div class="row">
                <div class="field full">
                    <label>提示词 (Prompt) <span class="required">*</span></label>
                    <textarea id="prompt" placeholder="描述你想要的视频内容，例如：一只猫在沙滩上散步，夕阳暖光，电影镜头" required></textarea>
                    <div id="promptHint" class="hint">描述视频内容、风格、光照、动作等</div>
                    
                    <!-- 提示词模板选择 -->
                    <div style="margin-top: 10px;">
                        <div style="font-size: 13px; color: var(--text2); margin-bottom: 6px;">📋 推荐提示词模板：</div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-secondary prompt-template-btn template-btn" data-template="t2v">
                                🎬 电影风格
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn template-btn" data-template="i2v">
                                ✨ 人物动起来
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn template-btn" data-template="multi">
                                🔄 图片转换
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn template-btn" data-template="keyframes">
                                🎯 关键帧过渡
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 反向提示词 -->
            <div class="row">
                <div class="field full">
                    <label>反向提示词 (Negative Prompt)</label>
                    <input id="negativePrompt" placeholder="不想出现的内容，如：模糊、变形" />
                </div>
            </div>

            <!-- 视频时长快速选择 -->
            <div class="row">
                <div class="field full">
                    <label>视频时长 (快速选择)</label>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="81" data-rate="24">
                            ~3秒 (81帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="121" data-rate="24">
                            ~5秒 (121帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="241" data-rate="24">
                            ~10秒 (241帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="441" data-rate="24">
                            ~18秒 (441帧/24fps)
                        </button>
                    </div>
                    <div style="margin-top: 12px; padding: 12px; background: var(--surface2); border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label style="margin: 0; font-size: 13px; color: var(--text2);">自定义时长:</label>
                            <input type="range" id="customDurationSlider" min="5" max="10" step="0.5" value="5" 
                                   style="flex: 1; cursor: pointer; accent-color: var(--accent);">
                            <span id="customDurationValue" style="min-width: 60px; text-align: right; font-weight: 600; color: var(--accent);">5.0秒</span>
                            <span style="font-size: 12px; color: var(--text2);">≈ <span id="customDurationFrames">120</span>帧</span>
                        </div>
                        <div class="hint" style="margin-top: 6px;">拖动滑块选择 5-10 秒之间的时长，自动计算对应帧数</div>
                    </div>
                </div>
            </div>

            <!-- 尺寸 & 帧数 -->
            <div class="row">
                <div class="field" id="widthField">
                    <label>宽度 (仅文生视频)</label>
                    <input type="number" id="width" value="1152" min="256" max="1920" />
                </div>
                <div class="field" id="heightField">
                    <label>高度 (仅文生视频)</label>
                    <input type="number" id="height" value="768" min="256" max="1920" />
                </div>
                <div class="field">
                    <label>帧数 (≤441, 8n+1)</label>
                    <input type="number" id="numFrames" value="121" min="9" max="441" step="8" />
                    <div class="hint">推荐 81, 121, 241, 441</div>
                </div>
                <div class="field">
                    <label>帧率 (1‑60)</label>
                    <input type="number" id="frameRate" value="24" min="1" max="60" />
                </div>
            </div>

            <!-- 推理步数 & 种子 -->
            <div class="row">
                <div class="field">
                    <label>推理步数</label>
                    <input type="number" id="inferenceSteps" value="30" min="1" max="100" />
                </div>
                <div class="field">
                    <label>随机种子 (留空随机)</label>
                    <input type="number" id="seed" placeholder="例如 42" />
                </div>
            </div>

            <!-- 提交 -->
                <div class="row" style="margin-top:8px;">
                    <div class="field full flex-center">
                        <button type="submit" class="btn" id="generateBtn">
                            <i class="fas fa-play"></i> 生成视频
                        </button>
                        <button type="button" class="btn btn-secondary" id="clearBtn">
                            <i class="fas fa-undo-alt"></i> 清空
                        </button>
                        <button type="button" class="btn btn-info" id="checkTaskBtn">
                            <i class="fas fa-search"></i> 检查任务状态
                        </button>
                        <button type="button" class="btn btn-danger" id="forceRetryBtn">
                            <i class="fas fa-bolt"></i> 强制重试
                        </button>
                    </div>
                </div>
            </form>

        <!-- 状态 & 结果 -->
        <div id="resultArea" class="card" style="display:none;">
            <h2><i class="fas fa-chart-simple"></i> 生成状态</h2>
            <div class="status-bar" id="statusBar">
                <span class="label">任务 ID：</span>
                <span class="value" id="taskIdDisplay">—</span>
                <span class="label">状态：</span>
                <span class="value" id="statusDisplay">等待</span>
                <span class="label">进度：</span>
                <div class="progress-track">
                    <div class="fill" id="progressFill"></div>
                </div>
                <span id="progressPercent">0%</span>
            </div>
            <div id="videoResult" style="margin-top:16px;"></div>
            <div id="errorDisplay" class="error-msg" style="display:none;"></div>
        </div>

        <p style="text-align:center;color:var(--text2);font-size:13px;margin-top:20px;">
            <i class="fas fa-shield-alt"></i> API Key 存储在服务器端，前端不接触密钥
        </p>
    </div>

    <script>
        (function() {
            const form = document.getElementById('generationForm');
            const modeSelect = document.getElementById('mode');
            const imageInput = document.getElementById('imageInput');
            const previewContainer = document.getElementById('previewContainer');
            const promptInput = document.getElementById('prompt');
            const negativeInput = document.getElementById('negativePrompt');
            const widthInput = document.getElementById('width');
            const heightInput = document.getElementById('height');
            const numFramesInput = document.getElementById('numFrames');
            const frameRateInput = document.getElementById('frameRate');
            const inferenceStepsInput = document.getElementById('inferenceSteps');
            const seedInput = document.getElementById('seed');
            const modelInput = document.getElementById('model');
            const generateBtn = document.getElementById('generateBtn');
            const clearBtn = document.getElementById('clearBtn');
            const checkTaskBtn = document.getElementById('checkTaskBtn');
            const forceRetryBtn = document.getElementById('forceRetryBtn');
            const resultArea = document.getElementById('resultArea');
            const taskIdDisplay = document.getElementById('taskIdDisplay');
            const statusDisplay = document.getElementById('statusDisplay');
            const progressFill = document.getElementById('progressFill');
            const progressPercent = document.getElementById('progressPercent');
            const videoResult = document.getElementById('videoResult');
            const errorDisplay = document.getElementById('errorDisplay');
            const imageLabel = document.getElementById('imageLabel');
            const imageHint = document.getElementById('imageHint');

            let uploadedFiles = [];
            let currentTaskId = null;
            let currentIdempotencyKey = null;
            let pollInterval = null;
            let isGenerating = false;

            function getAuthHeaders() {
                const token = localStorage.getItem('sanctum_token');
                const headers = { 'Content-Type': 'application/json' };
                if (token) {
                    headers['Authorization'] = 'Bearer ' + token;
                }
                return headers;
            }

            function generateUUID() {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    const r = Math.random() * 16 | 0;
                    const v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }

            function clearPendingTask() {
                sessionStorage.removeItem('pending_task_id');
                sessionStorage.removeItem('idempotency_key');
            }

            function startPolling(taskId) {
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(() => pollTaskStatus(taskId), 5000);
            }

            function updateProgress(percent) {
                progressFill.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
            }

            async function checkPendingTask() {
                const savedTaskId = sessionStorage.getItem('pending_task_id');
                if (!savedTaskId) return false;

                try {
                    const resp = await fetch(`/api/videos/${savedTaskId}`, { headers: getAuthHeaders() });
                    if (!resp.ok) return false;

                    const data = await resp.json();
                    taskIdDisplay.textContent = savedTaskId;

                    if (data.status === 'pending' || data.status === 'processing') {
                        currentTaskId = savedTaskId;
                        currentIdempotencyKey = sessionStorage.getItem('idempotency_key');
                        resultArea.style.display = 'block';
                        statusDisplay.textContent = '继续轮询中...';
                        generateBtn.disabled = true;
                        generateBtn.innerHTML = '<i class="fas fa-clock"></i> 等待任务完成...';
                        isGenerating = true;
                        startPolling(currentTaskId);
                        await pollTaskStatus(currentTaskId);
                        return true;
                    }

                    if (data.status === 'completed') {
                        displayVideoResult(data);
                    } else if (data.status === 'failed') {
                        showError(data.error_message || '任务失败');
                    }
                    clearPendingTask();
                } catch (err) {
                    console.error('检查任务失败:', err);
                }
                return false;
            }

            async function pollTaskStatus(taskId) {
                try {
                    const resp = await fetch(`/api/videos/${taskId}`, { headers: getAuthHeaders() });
                    if (!resp.ok) {
                        statusDisplay.textContent = '查询失败';
                        return;
                    }

                    const data = await resp.json();
                    statusDisplay.textContent = data.status;

                    if (data.status === 'completed') {
                        displayVideoResult(data);
                        clearPendingTask();
                        resetAfterDone();
                    } else if (data.status === 'failed') {
                        showError(data.error_message || '生成失败');
                        clearPendingTask();
                        resetAfterDone();
                    } else {
                        updateProgress(typeof data.progress === 'number' ? data.progress : 50);
                    }
                } catch (err) {
                    console.error('轮询失败:', err);
                }
            }

            function displayVideoResult(data) {
                if (data.video_url) {
                    videoResult.innerHTML = `
                        <div class="result-video">
                            <video controls autoplay loop>
                                <source src="${data.video_url}" type="video/mp4" />
                                您的浏览器不支持视频播放。
                            </video>
                        </div>
                        <p style="margin-top:8px;font-size:14px;color:var(--text2);">
                            <i class="fas fa-link"></i> <a href="${data.video_url}" target="_blank">直接打开视频</a>
                        </p>
                    `;
                } else {
                    videoResult.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: var(--text2);">
                            <i class="fas fa-check-circle" style="font-size: 48px; color: #22c55e; margin-bottom: 10px;"></i>
                            <p>视频生成完成</p>
                        </div>
                    `;
                }
                statusDisplay.textContent = '✅ 已完成';
                errorDisplay.style.display = 'none';
            }

            checkPendingTask();

            const promptTemplates = {
                t2v: 'A cinematic shot with beautiful lighting, realistic motion, high quality',
                i2v: 'The subject slowly moves with natural motion, cinematic camera movement, realistic details',
                multi: 'Create a smooth transformation scene between the reference images, cinematic lighting, consistent character identity, natural motion',
                keyframes: 'Create a smooth transition from the first keyframe to the second keyframe, maintaining character identity, consistent camera angle, and natural motion between scenes'
            };

            document.querySelectorAll('.prompt-template-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const template = this.dataset.template;
                    promptInput.value = promptTemplates[template] || promptTemplates.t2v;
                });
            });

            const durationBtns = document.querySelectorAll('.duration-btn');
            
            function setActiveDuration(btn) {
                durationBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                numFramesInput.value = btn.dataset.frames;
                frameRateInput.value = btn.dataset.rate;
            }
            
            durationBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    setActiveDuration(this);
                });
            });
            
            const defaultBtn = document.querySelector('.duration-btn[data-frames="121"]');
            if (defaultBtn) setActiveDuration(defaultBtn);

            const customDurationSlider = document.getElementById('customDurationSlider');
            const customDurationValue = document.getElementById('customDurationValue');
            const customDurationFrames = document.getElementById('customDurationFrames');

            function updateCustomDuration() {
                if (!customDurationSlider) return;

                const duration = parseFloat(customDurationSlider.value);
                const frames = Math.round(duration * 24);

                customDurationValue.textContent = duration.toFixed(1) + '秒';
                customDurationFrames.textContent = frames;
                numFramesInput.value = frames;
                frameRateInput.value = 24;

                durationBtns.forEach(b => b.classList.remove('active'));
            }

            if (customDurationSlider) {
                customDurationSlider.addEventListener('input', updateCustomDuration);
                updateCustomDuration();
            }

            function updatePreview() {
                previewContainer.innerHTML = '';
                const mode = modeSelect.value;
                uploadedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        const orderLabel = mode === 'keyframes' ? `关键帧 ${index + 1}`
                            : mode === 'multi' ? `图片 ${index + 1}`
                            : '';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="预览" />
                            ${orderLabel ? `<span class="preview-order">${orderLabel}</span>` : ''}
                            <button class="remove" data-index="${index}" title="移除"><i class="fas fa-times"></i></button>
                            ${index > 0 ? `<button class="reorder-btn reorder-left" data-index="${index}" data-dir="left" title="前移"><i class="fas fa-chevron-left"></i></button>` : ''}
                            ${index < uploadedFiles.length - 1 ? `<button class="reorder-btn reorder-right" data-index="${index}" data-dir="right" title="后移"><i class="fas fa-chevron-right"></i></button>` : ''}
                        `;
                        previewContainer.appendChild(div);
                        div.querySelector('.remove').addEventListener('click', function() {
                            const idx = parseInt(this.dataset.index);
                            uploadedFiles.splice(idx, 1);
                            updatePreview();
                        });
                        div.querySelectorAll('.reorder-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const idx = parseInt(this.dataset.index);
                                const dir = this.dataset.dir;
                                if (dir === 'left' && idx > 0) {
                                    [uploadedFiles[idx - 1], uploadedFiles[idx]] = [uploadedFiles[idx], uploadedFiles[idx - 1]];
                                } else if (dir === 'right' && idx < uploadedFiles.length - 1) {
                                    [uploadedFiles[idx], uploadedFiles[idx + 1]] = [uploadedFiles[idx + 1], uploadedFiles[idx]];
                                }
                                updatePreview();
                            });
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            imageInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                newFiles.forEach(newFile => {
                    const exists = uploadedFiles.some(f =>
                        f.name === newFile.name &&
                        f.size === newFile.size &&
                        f.lastModified === newFile.lastModified
                    );
                    if (!exists) {
                        uploadedFiles.push(newFile);
                    }
                });
                this.value = '';
                updatePreview();
            });

            function updateModeUI() {
                const mode = modeSelect.value;
                const showSizeFields = mode === 't2v';
                widthInput.parentElement.style.display = showSizeFields ? '' : 'none';
                heightInput.parentElement.style.display = showSizeFields ? '' : 'none';

                if (mode === 't2v') {
                    imageLabel.innerHTML = '图片 (文生视频模式不需要)';
                    imageInput.disabled = true;
                    imageInput.required = false;
                    imageHint.textContent = '文生视频无需上传图片';
                    promptInput.placeholder = '描述你想要的视频内容，例如：一只猫在沙滩上散步，夕阳暖光，电影镜头';
                    document.getElementById('promptHint').textContent = '描述视频内容、场景、风格、光照、动作等';
                } else {
                    imageInput.disabled = false;
                    imageInput.required = true;
                    if (mode === 'i2v') {
                        imageLabel.innerHTML = '上传图片 (图生视频) <span class="required">*</span>';
                        imageHint.textContent = '支持 JPG, PNG, WEBP，单张图片';
                        promptInput.placeholder = '描述图片中人物/物体的动作，例如：人物缓慢转身，自然表情，电影镜头';
                        document.getElementById('promptHint').textContent = '描述图片中人物/物体的动作、表情、相机运动等';
                    } else if (mode === 'multi') {
                        imageLabel.innerHTML = '上传多张图片 (多图视频) <span class="required">*</span>';
                        imageHint.textContent = '至少2张图片，可逐张选择添加。用预览图上的箭头按钮调整顺序';
                        promptInput.placeholder = '描述图片之间的转换场景，例如：创建两张图片之间的平滑过渡，电影级光照';
                        document.getElementById('promptHint').textContent = '描述参考图片之间的转换场景、风格一致性、过渡方式';
                    } else if (mode === 'keyframes') {
                        imageLabel.innerHTML = '上传关键帧图片 <span class="required">*</span>';
                        imageHint.textContent = '至少2张图片作为关键帧，可逐张选择添加。用箭头按钮调整关键帧顺序（第1帧→第2帧）';
                        promptInput.placeholder = '描述关键帧之间的过渡关系，例如：从第一帧平滑过渡到第二帧，保持角色一致性';
                        document.getElementById('promptHint').textContent = '描述关键帧之间的过渡关系、角色一致性、相机角度、场景过渡';
                    }
                }
                uploadedFiles = [];
                imageInput.value = '';
                updatePreview();
            }
            modeSelect.addEventListener('change', updateModeUI);
            updateModeUI();

            clearBtn.addEventListener('click', function() {
                form.reset();
                uploadedFiles = [];
                imageInput.value = '';
                updatePreview();
                resultArea.style.display = 'none';
                videoResult.innerHTML = '';
                errorDisplay.style.display = 'none';
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
                isGenerating = false;
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-play"></i> 生成视频';
                updateModeUI();
            });

            async function uploadImage(file) {
                const formData = new FormData();
                formData.append('image', file);
                
                const token = localStorage.getItem('sanctum_token');
                const headers = {};
                if (token) {
                    headers['Authorization'] = 'Bearer ' + token;
                }
                
                const resp = await fetch('/api/videos/upload', {
                    method: 'POST',
                    headers: headers,
                    body: formData,
                });
                
                if (!resp.ok) {
                    const errData = await resp.json().catch(() => ({}));
                    throw new Error(`图片上传失败 (${resp.status}): ${errData.error || '未知错误'}`);
                }
                
                const data = await resp.json();
                return data.url;
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (isGenerating) return;

                const prompt = promptInput.value.trim();
                if (!prompt) {
                    alert('请输入提示词');
                    return;
                }
                const mode = modeSelect.value;
                if (mode !== 't2v' && uploadedFiles.length === 0) {
                    alert('请上传图片');
                    return;
                }
                if ((mode === 'multi' || mode === 'keyframes') && uploadedFiles.length < 2) {
                    alert('多图/关键帧模式至少需要 2 张图片');
                    return;
                }

                isGenerating = true;
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 生成中...';
                resultArea.style.display = 'block';
                videoResult.innerHTML = '';
                errorDisplay.style.display = 'none';
                taskIdDisplay.textContent = '—';
                statusDisplay.textContent = '提交中';
                progressFill.style.width = '0%';
                progressPercent.textContent = '0%';

                const idempotencyKey = generateUUID();
                currentIdempotencyKey = idempotencyKey;

                try {
                    const imageUrls = [];
                    
                    if (mode !== 't2v') {
                        statusDisplay.textContent = '上传图片中...';
                        
                        for (const file of uploadedFiles) {
                            const url = await uploadImage(file);
                            imageUrls.push(url);
                        }
                    }

                    statusDisplay.textContent = '正在生成视频...';

                    const requestBody = {
                        model: modelInput.value,
                        prompt: prompt,
                        idempotency_key: idempotencyKey,
                        mode: mode,
                        width: parseInt(widthInput.value) || 1152,
                        height: parseInt(heightInput.value) || 768,
                        num_frames: parseInt(numFramesInput.value) || 121,
                        frame_rate: parseInt(frameRateInput.value) || 24,
                        num_inference_steps: parseInt(inferenceStepsInput.value) || 30,
                    };

                    if (negativeInput.value) {
                        requestBody.negative_prompt = negativeInput.value;
                    }
                    if (seedInput.value) {
                        requestBody.seed = parseInt(seedInput.value);
                    }
                    if (imageUrls.length > 0) {
                        if (mode === 'i2v') {
                            requestBody.image = imageUrls[0];
                        } else {
                            requestBody.image_urls = imageUrls;
                        }
                    }

                    const resp = await fetch('/api/videos', {
                        method: 'POST',
                        headers: getAuthHeaders(),
                        body: JSON.stringify(requestBody),
                    });

                    if (resp.status === 429) {
                        const data = await resp.json();
                        errorDisplay.innerHTML = `
                            <div class="info-banner">
                                <div class="info-banner-title">⚠️ ${data.error}</div>
                                <div class="info-banner-body">
                                    <p>任务 ID: ${data.task_id}</p>
                                    <p>请等待任务完成后再提交新任务</p>
                                </div>
                            </div>
                        `;
                        errorDisplay.style.display = 'block';
                        resetAfterDone();
                        return;
                    }

                    if (!resp.ok) {
                        const errText = await resp.text();
                        let errMsg = `视频生成失败 (${resp.status})`;
                        try {
                            const errJson = JSON.parse(errText);
                            if (resp.status === 401) {
                                errMsg = '❌ 请先登录';
                            } else {
                                errMsg += `: ${errJson.error || errJson.error_message || errText}`;
                            }
                        } catch (_) {
                            errMsg += `: ${errText.substring(0, 200)}`;
                        }
                        throw new Error(errMsg);
                    }

                    const data = await resp.json();
                    currentTaskId = data.task_id;
                    taskIdDisplay.textContent = currentTaskId;
                    statusDisplay.textContent = data.status || 'processing';

                    sessionStorage.setItem('pending_task_id', currentTaskId);
                    sessionStorage.setItem('idempotency_key', idempotencyKey);

                    if (data.status === 'completed' && data.video_url) {
                        displayVideoResult(data);
                        clearPendingTask();
                        resetAfterDone();
                    } else if (data.status === 'failed') {
                        showError(data.error_message || '生成失败');
                        clearPendingTask();
                        resetAfterDone();
                    } else {
                        startPolling(currentTaskId);
                    }

                } catch (err) {
                    console.error(err);
                    showError(err.message);
                    resetAfterDone();
                }
            });

            function showError(msg) {
                errorDisplay.textContent = `❌ ${msg}`;
                errorDisplay.style.display = 'block';
            }

            function resetAfterDone() {
                isGenerating = false;
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-play"></i> 生成视频';
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
            }

            window.addEventListener('beforeunload', function() {
                if (pollInterval) clearInterval(pollInterval);
            });

            checkTaskBtn.addEventListener('click', async function() {
                checkTaskBtn.disabled = true;
                checkTaskBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 检查中...';
                statusDisplay.textContent = '检查任务状态...';
                resultArea.style.display = 'block';
                errorDisplay.style.display = 'none';

                try {
                    const savedTaskId = sessionStorage.getItem('pending_task_id');
                    if (!savedTaskId) {
                        statusDisplay.textContent = '无进行中的任务';
                        return;
                    }

                    const resp = await fetch(`/api/videos/${savedTaskId}`, { headers: getAuthHeaders() });
                    if (!resp.ok) {
                        statusDisplay.textContent = '无进行中的任务';
                        clearPendingTask();
                        return;
                    }

                    const data = await resp.json();
                    taskIdDisplay.textContent = savedTaskId;
                    statusDisplay.textContent = data.status;

                    if (data.status === 'pending' || data.status === 'processing') {
                        errorDisplay.innerHTML = `
                            <div class="info-banner">
                                <div class="info-banner-title">⚠️ 有任务正在进行中</div>
                                <div class="info-banner-body">
                                    <p>任务 ID: ${savedTaskId}</p>
                                    <p>状态: ${data.status}</p>
                                </div>
                            </div>
                        `;
                        errorDisplay.style.display = 'block';
                        startPolling(savedTaskId);
                    } else if (data.status === 'completed') {
                        displayVideoResult(data);
                        clearPendingTask();
                    } else if (data.status === 'failed') {
                        showError(data.error_message || '任务失败');
                        clearPendingTask();
                    }
                } catch (err) {
                    statusDisplay.textContent = '检查失败';
                    console.error(err);
                } finally {
                    checkTaskBtn.disabled = false;
                    checkTaskBtn.innerHTML = '<i class="fas fa-search"></i> 检查任务状态';
                }
            });

            forceRetryBtn.addEventListener('click', function() {
                if (confirm('⚠️ 强制重试可能会继续失败。\n\n建议先点击"检查任务状态"确认队列空闲后再重试。\n\n确定要继续强制重试吗？')) {
                    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            });

        })();
    </script>
</body>
</html>