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
                    <i class="fas fa-info-circle"></i> 图片会转为 Base64 发送
                </span>
            </div>
        </header>

        <form id="generationForm" class="card">
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
                    <input value="agnes-video-v2.0" disabled style="opacity:0.7;" />
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
                            <button type="button" class="btn btn-secondary prompt-template-btn" data-template="t2v" style="padding: 6px 12px; font-size: 12px;">
                                🎬 电影风格
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn" data-template="i2v" style="padding: 6px 12px; font-size: 12px;">
                                ✨ 人物动起来
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn" data-template="multi" style="padding: 6px 12px; font-size: 12px;">
                                🔄 图片转换
                            </button>
                            <button type="button" class="btn btn-secondary prompt-template-btn" data-template="keyframes" style="padding: 6px 12px; font-size: 12px;">
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
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="81" data-rate="24" style="padding: 8px 16px; font-size: 14px;">
                            ~3秒 (81帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="121" data-rate="24" style="padding: 8px 16px; font-size: 14px;">
                            ~5秒 (121帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="241" data-rate="24" style="padding: 8px 16px; font-size: 14px;">
                            ~10秒 (241帧/24fps)
                        </button>
                        <button type="button" class="btn btn-secondary duration-btn" data-frames="441" data-rate="24" style="padding: 8px 16px; font-size: 14px;">
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
                <div class="field">
                    <label>宽度</label>
                    <input type="number" id="width" value="1152" min="256" max="1920" />
                </div>
                <div class="field">
                    <label>高度</label>
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
                        <button type="button" class="btn btn-secondary" id="checkTaskBtn" style="background: #4a9eff; border-color: #4a9eff;">
                            <i class="fas fa-search"></i> 检查任务状态
                        </button>
                        <button type="button" class="btn btn-secondary" id="forceRetryBtn" style="background: #ff6b4a; border-color: #ff6b4a;">
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
            <i class="fas fa-shield-alt"></i> 你的 API Key 仅存储在本地浏览器，不会上传
        </p>
    </div>

    <script>
        (function() {
            // DOM refs
            const form = document.getElementById('generationForm');
            const apiKeyInput = document.getElementById('apiKey') || { value: '' }; // 兼容已移除的情况
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

            let uploadedFiles = []; // 存储 File 对象
            let currentTaskId = null;
            let currentVideoId = null;
            let pollInterval = null;
            let isGenerating = false;
            let lastErrorDetail = null; // 存储错误详情
            let pendingTaskInfo = null; // 进行中的任务信息

            // ----- 任务管理：检查是否有进行中的任务 -----
            async function checkPendingTask() {
                try {
                    const resp = await fetch('/api/video/task/check');
                    const data = await resp.json();
                    
                    if (data.success && data.has_pending_task) {
                        pendingTaskInfo = data.task;
                        
                        // 显示任务信息
                        resultArea.style.display = 'block';
                        taskIdDisplay.textContent = pendingTaskInfo.task_id || '—';
                        statusDisplay.textContent = '继续轮询中...';
                        currentTaskId = pendingTaskInfo.task_id;
                        currentVideoId = pendingTaskInfo.video_id;
                        
                        // 显示提示
                        errorDisplay.innerHTML = `
                            <div style="background: rgba(42,157,255,0.15); padding: 12px; border-radius: 8px; margin-bottom: 10px;">
                                <div style="font-weight: 600; margin-bottom: 6px;">⚠️ 有任务正在进行中</div>
                                <div style="font-size: 13px;">
                                    <p>任务 ID: ${pendingTaskInfo.task_id}</p>
                                    <p>提示词: ${pendingTaskInfo.prompt.substring(0, 50)}...</p>
                                    <p>创建时间: ${pendingTaskInfo.created_at}</p>
                                </div>
                                <div style="margin-top: 8px; font-size: 13px; color: var(--text2);">
                                    系统正在自动查询任务状态，完成后将显示视频结果。
                                </div>
                            </div>
                        `;
                        errorDisplay.style.display = 'block';
                        
                        // 禁用生成按钮
                        generateBtn.disabled = true;
                        generateBtn.innerHTML = '<i class="fas fa-clock"></i> 等待任务完成...';
                        isGenerating = true;
                        
                        // 开始轮询
                        if (pollInterval) clearInterval(pollInterval);
                        pollInterval = setInterval(() => pollTaskStatus(currentVideoId), 3000);
                        pollTaskStatus(currentVideoId);
                        
                        return true;
                    }
                    return false;
                } catch (err) {
                    console.error('检查任务失败:', err);
                    return false;
                }
            }

            // ----- 任务管理：通过后端查询任务状态 -----
            async function pollTaskStatus(videoId) {
                try {
                    const resp = await fetch(`/api/video/task/status?video_id=${videoId}`);
                    const data = await resp.json();
                    
                    if (!data.success) {
                        statusDisplay.textContent = '查询失败';
                        return;
                    }
                    
                    const task = data.task;
                    const status = task.status;
                    
                    statusDisplay.textContent = status;
                    
                    if (status === 'completed') {
                        clearInterval(pollInterval);
                        pollInterval = null;
                        
                        // 优先使用 Agnes API 返回的 remixed_from_video_id
                        const videoUrl = task.result_url 
                            || data.remixed_from_video_id 
                            || data.video_url 
                            || null;
                        
                        if (videoUrl) {
                            videoResult.innerHTML = `
                                <video controls autoplay loop style="max-width: 100%; border-radius: 12px;">
                                    <source src="${videoUrl}" type="video/mp4">
                                </video>
                                <div style="margin-top: 10px; text-align: center;">
                                    <a href="${videoUrl}" download class="btn btn-primary">
                                        <i class="fas fa-download"></i> 下载视频
                                    </a>
                                </div>
                            `;
                        } else {
                            videoResult.innerHTML = `
                                <div style="padding: 20px; text-align: center; color: var(--text2);">
                                    <i class="fas fa-check-circle" style="font-size: 48px; color: #22c55e; margin-bottom: 10px;"></i>
                                    <p>视频生成完成</p>
                                    <p style="font-size: 13px;">video_id: ${videoId}</p>
                                </div>
                            `;
                        }
                        statusDisplay.textContent = '✅ 已完成';
                        errorDisplay.style.display = 'none';
                        
                        // 重置按钮
                        resetAfterDone();
                        pendingTaskInfo = null;
                        return;
                    } else if (status === 'failed') {
                        clearInterval(pollInterval);
                        showError('任务失败: ' + (task.error_message || '未知错误'));
                        resetAfterDone();
                        pendingTaskInfo = null;
                    }
                } catch (err) {
                    console.error('轮询失败:', err);
                }
            }

            // ----- 任务管理：保存任务信息到后端 -----
            async function saveTaskInfo(taskId, videoId, prompt, mode, numFrames, frameRate) {
                try {
                    const resp = await fetch('/api/video/task/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            task_id: taskId,
                            video_id: videoId,
                            prompt: prompt,
                            mode: mode,
                            num_frames: numFrames,
                            frame_rate: frameRate,
                        }),
                    });
                    
                    const data = await resp.json();
                    console.log('任务已保存:', data);
                } catch (err) {
                    console.error('保存任务失败:', err);
                }
            }

            // 页面加载时检查是否有进行中的任务
            checkPendingTask();

            // ----- 提示词模板按钮 -----
            document.querySelectorAll('.prompt-template-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const template = this.dataset.template;
                    promptInput.value = promptTemplates[template] || promptTemplates.t2v;
                });
            });

            // ----- 视频时长快速选择 -----
            const durationBtns = document.querySelectorAll('.duration-btn');
            
            function setActiveDuration(btn) {
                // 移除所有按钮的激活状态
                durationBtns.forEach(b => {
                    b.style.cssText = 'background: var(--surface2); border: 1px solid var(--border); color: var(--text2); padding: 8px 16px; font-size: 14px;';
                    b.classList.remove('active');
                });
                // 激活当前按钮
                btn.style.cssText = 'background: #2a9dff; border: 1px solid #2a9dff; color: #fff; padding: 8px 16px; font-size: 14px;';
                btn.classList.add('active');
                
                // 设置参数
                const frames = btn.dataset.frames;
                const rate = btn.dataset.rate;
                document.getElementById('numFrames').value = frames;
                document.getElementById('frameRate').value = rate;
            }
            
            durationBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    setActiveDuration(this);
                });
            });
            
            // 默认激活 5 秒选项
            const defaultBtn = document.querySelector('.duration-btn[data-frames="121"]');
            if (defaultBtn) setActiveDuration(defaultBtn);

            // ----- 自定义时长滑块 -----
            const customDurationSlider = document.getElementById('customDurationSlider');
            const customDurationValue = document.getElementById('customDurationValue');
            const customDurationFrames = document.getElementById('customDurationFrames');

            function updateCustomDuration() {
                if (!customDurationSlider) return;
                
                const duration = parseFloat(customDurationSlider.value);
                const frameRate = 24;
                const frames = Math.round(duration * frameRate);
                
                if (customDurationValue) customDurationValue.textContent = duration.toFixed(1) + '秒';
                if (customDurationFrames) customDurationFrames.textContent = frames;
                
                // 同步更新 numFrames 输入框
                const numFramesInput = document.getElementById('numFrames');
                const frameRateInput = document.getElementById('frameRate');
                if (numFramesInput) numFramesInput.value = frames;
                if (frameRateInput) frameRateInput.value = frameRate;
                
                // 移除快速选择按钮的激活状态
                document.querySelectorAll('.duration-btn').forEach(b => {
                    b.style.cssText = 'background: var(--surface2); border: 1px solid var(--border); color: var(--text2); padding: 8px 16px; font-size: 14px;';
                    b.classList.remove('active');
                });
            }

            if (customDurationSlider) {
                customDurationSlider.addEventListener('input', updateCustomDuration);
                // 初始化
                updateCustomDuration();
            }

            // ----- 图片预览管理 -----
            function updatePreview() {
                previewContainer.innerHTML = '';
                uploadedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="预览" />
                            <button class="remove" data-index="${index}" title="移除"><i class="fas fa-times"></i></button>
                        `;
                        previewContainer.appendChild(div);
                        div.querySelector('.remove').addEventListener('click', function() {
                            const idx = parseInt(this.dataset.index);
                            uploadedFiles.splice(idx, 1);
                            // 更新 input.files
                            const dt = new DataTransfer();
                            uploadedFiles.forEach(f => dt.items.add(f));
                            imageInput.files = dt.files;
                            updatePreview();
                        });
                    };
                    reader.readAsDataURL(file);
                });
            }

            imageInput.addEventListener('change', function() {
                const files = Array.from(this.files);
                uploadedFiles = files;
                updatePreview();
            });

            // 提示词模板
            const promptTemplates = {
                t2v: 'A cinematic shot with beautiful lighting, realistic motion, high quality',
                i2v: 'The subject slowly moves with natural motion, cinematic camera movement, realistic details',
                multi: 'Create a smooth transformation scene between the reference images, cinematic lighting, consistent character identity, natural motion',
                keyframes: 'Create a smooth transition from the first keyframe to the second keyframe, maintaining character identity, consistent camera angle, and natural motion between scenes'
            };

            // ----- 模式切换 -----
            function updateModeUI() {
                const mode = modeSelect.value;
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
                        imageHint.textContent = '请选择多张图片（至少2张），按顺序作为参考帧';
                        promptInput.placeholder = '描述图片之间的转换场景，例如：创建两张图片之间的平滑过渡，电影级光照';
                        document.getElementById('promptHint').textContent = '描述参考图片之间的转换场景、风格一致性、过渡方式';
                    } else if (mode === 'keyframes') {
                        imageLabel.innerHTML = '上传多张图片 (关键帧动画) <span class="required">*</span>';
                        imageHint.textContent = '请选择多张图片（至少2张），按顺序作为关键帧';
                        promptInput.placeholder = '描述关键帧之间的过渡关系，例如：从第一帧平滑过渡到第二帧，保持角色一致性';
                        document.getElementById('promptHint').textContent = '描述关键帧之间的过渡关系、角色一致性、相机角度、场景过渡';
                    }
                }
                // 清空已有文件
                uploadedFiles = [];
                imageInput.value = '';
                updatePreview();
            }
            modeSelect.addEventListener('change', updateModeUI);
            updateModeUI();

            // ----- 清空 -----
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
                // 重置模式UI
                updateModeUI();
            });

            // ----- 上传图片到服务器获取 URL -----
            async function uploadImage(file) {
                const formData = new FormData();
                formData.append('image', file);
                
                const resp = await fetch('/api/video/upload', {
                    method: 'POST',
                    body: formData,
                });
                
                if (!resp.ok) {
                    const errData = await resp.json().catch(() => ({}));
                    throw new Error(`图片上传失败 (${resp.status}): ${errData.error || '未知错误'}`);
                }
                
                const data = await resp.json();
                
                // 服务器已返回完整 URL，直接使用
                return data.url;
            }

            // ----- 读取文件为纯 Base64 (不含前缀) -----
            function readFileAsBase64(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // e.target.result 是 data:image/...;base64,xxxx
                        const base64 = e.target.result.split(',')[1];
                        if (!base64) {
                            reject(new Error('无法提取 Base64 数据'));
                            return;
                        }
                        // 移除所有空白字符（换行、空格等），防止 padding 问题
                        const clean = base64.replace(/\s/g, '');
                        resolve(clean);
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            // ----- 简化的 fetch 封装 -----
            async function fetchOnce(url, options) {
                try {
                    statusDisplay.textContent = `提交中...`;
                    const response = await fetch(url, options);
                    return response;
                } catch (err) {
                    throw new Error(`网络请求失败: ${err.message}`);
                }
            }

            // ----- 主提交 -----
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (isGenerating) return;

                // 先检查是否有进行中的任务
                if (pendingTaskInfo) {
                    alert('有任务正在进行中，请等待完成后再生成新视频。\n任务 ID: ' + pendingTaskInfo.task_id);
                    return;
                }

                // 再次检查（防止页面刷新后状态丢失）
                const hasPending = await checkPendingTask();
                if (hasPending) {
                    return;
                }

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

                // 禁用按钮
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

                // 构建请求体
                const requestBody = {
                    model: 'agnes-video-v2.0',
                    prompt: prompt,
                    height: parseInt(heightInput.value) || 768,
                    width: parseInt(widthInput.value) || 1152,
                    num_frames: parseInt(numFramesInput.value) || 121,
                    frame_rate: parseFloat(frameRateInput.value) || 24,
                    num_inference_steps: parseInt(inferenceStepsInput.value) || 30,
                };
                if (negativeInput.value) {
                    requestBody.negative_prompt = negativeInput.value;
                }
                if (seedInput.value) {
                    requestBody.seed = parseInt(seedInput.value);
                }

                // 处理图片：先上传到服务器获取 URL
                try {
                    if (mode !== 't2v') {
                        statusDisplay.textContent = '上传图片中...';
                        
                        if (mode === 'i2v') {
                            // 单图
                            const file = uploadedFiles[0];
                            const imageUrl = await uploadImage(file);
                            requestBody.image = imageUrl;
                        } else {
                            // multi 或 keyframes
                            const imageUrls = [];
                            for (const file of uploadedFiles) {
                                const url = await uploadImage(file);
                                imageUrls.push(url);
                            }
                            requestBody.extra_body = {
                                image: imageUrls,
                            };
                            if (mode === 'keyframes') {
                                requestBody.extra_body.mode = 'keyframes';
                            }
                        }
                    }
                    
                    statusDisplay.textContent = '提交任务...';

                    // 调用后端API创建视频
                    const createUrl = '/api/video/create';
                    const createResp = await fetchOnce(createUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestBody),
                    });

                    // 处理特殊错误（如 503）
                    if (createResp.status === 503) {
                        const detailMsg = createResp.errorDetail || '服务繁忙，当前有任务正在处理';
                        lastErrorDetail = detailMsg;
                        
                        // 检测是否是"任务队列已满"错误
                        const isQueueFull = detailMsg.includes('tasks:') || detailMsg.includes('Service busy');
                        
                        errorDisplay.innerHTML = `
                            <div style="margin-bottom: 10px;">
                                <strong>❌ 服务繁忙 (503)</strong>
                            </div>
                            ${isQueueFull ? `
                            <div style="background: rgba(255,107,74,0.15); padding: 12px; border-radius: 8px; margin-bottom: 10px; font-size: 13px;">
                                <div style="font-weight: 600; margin-bottom: 6px;">⚠️ 检测到账户已有任务在队列中</div>
                                <div>错误信息：${detailMsg}</div>
                            </div>
                            <div style="font-size: 13px;">
                                <strong>解决方法：</strong>
                                <ol style="margin: 8px 0 0 20px; line-height: 1.8;">
                                    <li>登录 <a href="https://agnes-ai.com" target="_blank" style="color: var(--accent);">Agnes AI 平台</a></li>
                                    <li>进入"我的任务"或"任务历史"</li>
                                    <li>取消或等待当前正在执行的任务</li>
                                    <li>任务完成后，再点击"生成视频"</li>
                                </ol>
                            </div>
                            ` : `
                            <div style="margin-top: 8px; font-size: 13px;">
                                ${detailMsg.replace(/\n/g, '<br/>')}
                            </div>
                            <div style="margin-top: 10px; font-size: 13px;">
                                💡 建议：等待 1-2 分钟后重试
                            </div>
                            `}
                        `;
                        errorDisplay.style.display = 'block';
                        resetAfterDone();
                        return;
                    }

                    if (!createResp.ok) {
                        let errMsg = `创建任务失败 (${createResp.status})`;
                        try {
                            const errJson = await createResp.json();
                            
                            // 友好化错误提示
                            if (createResp.status === 401) {
                                errMsg = '❌ API Key 无效或已过期，请检查';
                            } else if (createResp.status === 402) {
                                errMsg = '❌ 余额不足，请充值后重试';
                            } else if (createResp.status === 429) {
                                errMsg = '❌ 请求过于频繁，请稍后再试';
                            } else {
                                errMsg += `: ${JSON.stringify(errJson)}`;
                            }
                        } catch (_) {
                            const errText = await createResp.text();
                            errMsg += `: ${errText}`;
                        }
                        throw new Error(errMsg);
                    }

                    const createData = await createResp.json();
                    const taskId = createData.task_id || createData.id;
                    const videoId = createData.video_id;
                    
                    // 如果没有 video_id 且没有 task_id，说明创建失败
                    if (!taskId && !videoId) {
                        throw new Error('创建任务失败：API 未返回任务 ID');
                    }
                    
                    currentTaskId = taskId;
                    currentVideoId = videoId;
                    taskIdDisplay.textContent = taskId || '—';
                    statusDisplay.textContent = '已提交';

                    // 如果有 video_id 才保存任务和轮询
                    if (videoId) {
                        // 保存任务信息到后端
                        await saveTaskInfo(
                            taskId,
                            videoId,
                            prompt,
                            mode,
                            parseInt(numFramesInput.value) || 121,
                            parseFloat(frameRateInput.value) || 24
                        );

                        // 开始轮询（通过后端API）
                        if (pollInterval) clearInterval(pollInterval);
                        pollInterval = setInterval(() => pollTask(videoId), 2000);
                        // 立即轮询一次
                        await pollTask(videoId);
                    } else {
                        // 只有 task_id，没有 video_id 的情况
                        statusDisplay.textContent = '已提交（无 video_id，请手动查询）';
                        resetAfterDone();
                    }

                } catch (err) {
                    console.error(err);
                    showError(err.message);
                    resetAfterDone();
                }
            });

            // ----- 轮询（通过后端API）-----
            async function pollTask(videoId) {
                if (!videoId) {
                    statusDisplay.textContent = '无 video_id，无法查询';
                    return;
                }
                
                try {
                    // 调用后端API查询任务状态
                    const resp = await fetch(`/api/video/task/status?video_id=${encodeURIComponent(videoId)}`);
                    const data = await resp.json();
                    
                    if (!data.success) {
                        statusDisplay.textContent = '查询失败';
                        return;
                    }
                    
                    const task = data.task;
                    const status = task.status;
                    const progress = task.progress || 0;

                    statusDisplay.textContent = status || 'unknown';
                    progressFill.style.width = `${progress}%`;
                    progressPercent.textContent = `${progress}%`;

                    if (status === 'completed') {
                        clearInterval(pollInterval);
                        pollInterval = null;
                        const videoUrl = task.result_url || data.agnes_response?.remixed_from_video_id;
                        if (videoUrl) {
                            videoResult.innerHTML = `
                                <div class="result-video">
                                    <video controls autoplay loop>
                                        <source src="${videoUrl}" type="video/mp4" />
                                        您的浏览器不支持视频播放。
                                    </video>
                                </div>
                                <p style="margin-top:8px;font-size:14px;color:var(--text2);">
                                    <i class="fas fa-link"></i> <a href="${videoUrl}" target="_blank">直接打开视频</a>
                                </p>
                            `;
                        } else {
                            showError('任务完成但未返回视频 URL');
                        }
                        resetAfterDone();
                        return;
                    }

                    if (status === 'failed') {
                        clearInterval(pollInterval);
                        pollInterval = null;
                        const errMsg = task.error_message || data.agnes_response?.error?.message || '生成失败，请稍后重试';
                        showError(errMsg);
                        resetAfterDone();
                        return;
                    }

                    // 其他状态继续
                    statusDisplay.textContent = status || 'processing';

                } catch (err) {
                    console.warn('轮询异常:', err);
                }
            }

            // ----- 辅助 -----
            function showError(msg) {
                errorDisplay.textContent = `❌ ${msg}`;
                errorDisplay.style.display = 'block';
            }

            function resetAfterDone() {
                isGenerating = false;
                generateBtn.disabled = false;
                generateBtn.innerHTML = '<i class="fas fa-play"></i> 生成视频';
                pendingTaskInfo = null; // 清空进行中的任务信息
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
            }

            window.addEventListener('beforeunload', function() {
                if (pollInterval) clearInterval(pollInterval);
            });

            // ----- 检查任务状态 -----
            checkTaskBtn.addEventListener('click', async function() {
                checkTaskBtn.disabled = true;
                checkTaskBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 检查中...';
                statusDisplay.textContent = '检查任务状态...';
                resultArea.style.display = 'block';
                errorDisplay.style.display = 'none';
                
                try {
                    // 调用后端API检查任务状态
                    const resp = await fetch('/api/video/task/check');
                    const data = await resp.json();
                    
                    if (data.success && data.has_pending_task) {
                        const task = data.task;
                        statusDisplay.textContent = '有任务进行中';
                        taskIdDisplay.textContent = task.task_id || '—';
                        errorDisplay.innerHTML = `
                            <div style="background: rgba(42,157,255,0.15); padding: 12px; border-radius: 8px;">
                                <div style="font-weight: 600; margin-bottom: 6px;">⚠️ 有任务正在进行中</div>
                                <div style="font-size: 13px;">
                                    <p>任务 ID: ${task.task_id}</p>
                                    <p>提示词: ${(task.prompt || '').substring(0, 50)}...</p>
                                </div>
                            </div>
                        `;
                        errorDisplay.style.display = 'block';
                    } else {
                        statusDisplay.textContent = '无进行中的任务';
                        errorDisplay.style.display = 'none';
                    }
                } catch (err) {
                    statusDisplay.textContent = '检查失败';
                    console.error(err);
                } finally {
                    checkTaskBtn.disabled = false;
                    checkTaskBtn.innerHTML = '<i class="fas fa-search"></i> 检查任务状态';
                }
            });

            // ----- 强制重试 -----
            forceRetryBtn.addEventListener('click', function() {
                if (!lastErrorDetail) {
                    form.dispatchEvent(new Event('submit'));
                    return;
                }
                
                // 显示确认对话框
                if (confirm('⚠️ 强制重试可能会继续失败，因为服务繁忙。\n\n建议先点击"检查任务状态"确认队列空闲后再重试。\n\n确定要继续强制重试吗？')) {
                    form.dispatchEvent(new Event('submit'));
                }
            });

        })();
    </script>
</body>
</html>