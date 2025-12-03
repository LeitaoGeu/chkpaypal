<?php

error_reporting(0);

session_start();

require_once 'config.php';

// Simulação de dados para o frontend (mantidos para que o JavaScript não quebre)
if (!isset($_SESSION['token_data']['expiry'])) {
    $future_time = time() + (24 * 60 * 60); // 24 horas de validade
    $_SESSION['token_data']['expiry'] = date('Y-m-d H:i:s', $future_time);
}
$_SESSION['token_valid'] = 'FAKE_SECURE_TOKEN_12345';

// VALORES FIXOS
$fixedApi = 'apis/paypal.php'; // Caminho fixo para sua API
$fixedThreads = 4; // Threads fixas

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <title>CHECKER PAYPAL</title>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style type="text/css">
        /* Cores Base */
        :root {
            --dark-bg: #0a0c1b;
            --medium-bg: #1e1e3c;
            --box-bg: rgba(20, 22, 48, 0.95);
            --primary-blue: #1e90ff; /* Azul Elétrico */
            --highlight-cyan: #00ffff; /* Ciano Neon para destaques */
            --live-green: #00ff7f;
            --die-red: #ff4444;
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--medium-bg) 100%);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .main-content {
            padding: 30px;
            width: 95%;
            max-width: 1000px;
            margin: auto;
        }

        /* BARRA DE BOTÕES E OBS */
        .top-bar {
            background: var(--box-bg);
            padding: 15px 20px;
            border-radius: 20px;
            border: 2px solid var(--primary-blue);
            box-shadow: 0 0 30px rgba(30, 144, 255, 0.6);
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        .obs-area {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(30, 144, 255, 0.3);
        }

        .obs-area span {
            color: var(--primary-blue);
            font-style: italic;
            font-size: 0.85rem;
            display: block;
        }
        
        .control-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        
        /* ESTILO GERAL DOS BOTÕES */
        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 600;
            color: #fff;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
            color: #fff;
        }

        /* CORES ESPECÍFICAS DOS BOTÕES */
        .btn-success { background: linear-gradient(45deg, #00b359, var(--live-green)); }
        .btn-warning { background: linear-gradient(45deg, #ffaa00, #ff7700); }
        .btn-danger { background: linear-gradient(45deg, #cc3333, var(--die-red)); }
        .btn-info { background: linear-gradient(45deg, var(--primary-blue), var(--highlight-cyan)); }
        .btn-gerador-style { background: linear-gradient(45deg, #1e90ff, #00ffff); } 


        /* ÁREA DE CONTEÚDO PRINCIPAL (INPUTS/RESULTADOS) */
        .content-area {
            background: var(--box-bg);
            border-radius: 20px;
            padding: 25px;
            border: 2px solid var(--primary-blue);
            box-shadow: 0 0 30px rgba(30, 144, 255, 0.6);
            transition: all 0.3s ease;
        }
        
        .textarea-container {
            margin-bottom: 25px;
        }
        textarea {
            width: 100%;
            min-height: 250px;
            background: var(--dark-bg);
            color: #fff;
            border: 2px solid var(--primary-blue);
            border-radius: 12px;
            padding: 15px;
            resize: vertical;
            font-family: monospace;
            font-size: 14px;
        }

        /* PROGRESSO E STATUS BAR */
        .progress {
            height: 25px;
            background: var(--dark-bg);
            border: 2px solid var(--primary-blue);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
            background: linear-gradient(90deg, var(--primary-blue), var(--highlight-cyan));
            font-weight: 600;
        }
        
        /* STATUS BAR - AJUSTADA PARA ABRIGAR 5 ITENS */
        .status-bar {
            display: flex;
            justify-content: center; 
            margin: 25px 0;
            flex-wrap: wrap;
            gap: 10px; 
        }
        .status-item {
            background: rgba(30, 144, 255, 0.15);
            padding: 12px 15px; 
            border-radius: 12px;
            border: 2px solid var(--primary-blue);
            min-width: 100px; 
            text-align: center;
        }
        .status-item span {
            font-weight: 700;
            color: var(--live-green);
            font-size: 1.2rem;
            display: block;
        }
        
        /* RESULTADOS E ESTATÍSTICAS CORRIGIDAS */
        .result-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .result-box {
            flex: 1 1 calc(50% - 10px); 
            background: rgba(10, 12, 27, 0.98);
            border: 2px solid var(--primary-blue);
            border-radius: 12px;
            padding: 20px;
            max-height: 350px;
            overflow-y: auto;
            min-width: 300px; 
        }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .result-item {
            font-family: monospace;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px dotted rgba(255, 255, 255, 0.1);
            color: #fff;
            word-break: break-all;
        }
        .result-item:last-child {
            border-bottom: none;
        }

        /* Estilo do Modal */
        .modal-content-custom {
            background: var(--box-bg);
            border: 3px solid var(--primary-blue);
            border-radius: 20px;
            box-shadow: 0 0 50px rgba(0, 255, 255, 0.8);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .modal-header-custom {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: none;
        }
        .exclamation-icon {
            font-size: 3.5rem;
            color: var(--highlight-cyan);
            margin-bottom: 10px;
            text-shadow: 0 0 10px var(--highlight-cyan);
        }
        .modal-title-custom {
            color: var(--highlight-cyan);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .modal-body-custom {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .modal-footer-custom {
            border-top: none;
            justify-content: center;
        }
        .btn-modal-start {
            background: linear-gradient(45deg, #1e90ff, #00ffff);
            font-size: 1.2rem;
            padding: 12px 30px;
            border-radius: 15px;
            transition: all 0.3s;
        }
        .btn-modal-start:hover {
            box-shadow: 0 0 25px rgba(0, 255, 255, 0.7);
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .result-box {
                flex: 1 1 100%;
            }
        }
        @media (max-width: 991px) {
            .top-bar { flex-direction: column; }
            .control-group { width: auto; justify-content: center; }
            .modal-title-custom { font-size: 2rem; }
            .modal-body-custom { font-size: 1rem; }
        }
    </style>
</head>

<body>

    <input type="hidden" value="<?php echo $_SESSION['token_valid']; ?>" id="token_api">
    
    <div class="modal fade" id="welcomeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-custom">
          
          <div class="modal-header modal-header-custom">
            <i class="fas fa-exclamation-triangle exclamation-icon"></i>
            <h1 class="modal-title modal-title-custom" id="welcomeModalLabel">BEM VINDO</h1>
          </div>
          
          <div class="modal-body modal-body-custom">
            <p>você está usando uma ferramenta que foi disponibilizada gratuitamente, não se coloque no direito de dizer que a mesma é ruim ou que não presta.</p>
            <p style="font-style: italic; color: var(--primary-blue); margin-top: 20px;">
                Dica: Caso o checker venha a retornar N/A ou iniciar os testes e não retornar nada, considere utilizar vpn ou proxy, não utilize cvv 000, para garantir durabilidade da api.
            </p>
          </div>
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-modal-start" data-bs-dismiss="modal">INICIAR TESTES</button>
          </div>
        </div>
      </div>
    </div>
    <div class="main-content">
        
        <div class="top-bar">
            <div class="obs-area">
                <h2 style="color: var(--highlight-cyan); font-weight: 700; font-size: 1.5rem; margin: 0;">CHECKER PAYPAL</h2>
                <span style="font-style: normal; margin-top: 5px;">Sistema 4 Theards - 0Auth</span>
            </div>

            <div class="control-group">
                <button class="btn btn-success" id="chk-start" title="Iniciar"><i class="fas fa-play"></i> Iniciar</button>
                
                <button class="btn btn-gerador-style" id="btn-gerador" title="Abrir Gerador"><i class="fas fa-credit-card"></i> Gerador</button>
                
                <button class="btn btn-warning" id="chk-pause" disabled title="Pausar"><i class="fas fa-pause"></i> Pausar</button>
                <button class="btn btn-danger" id="chk-stop" disabled title="Parar"><i class="fas fa-stop"></i> Parar</button>
                <button class="btn btn-info" id="chk-clean" title="Limpar"><i class="fas fa-trash"></i> Limpar</button>
            </div>
        </div>
        
        <div class="content-area">
            <div class="progress">
                <div id="progress-bar" class="progress-bar" style="width: 0%;">0%</div>
            </div>
            
            <div class="status-bar">
                <div class="status-item">Carregadas: <span id="total-count">0</span></div>
                
                <div class="status-item">Testadas: <span id="tested-count">0</span></div> 
                
                <div class="status-item">Live: <span id="live-count">0</span></div>
                <div class="status-item">Die: <span id="die-count">0</span></div>
                <div class="status-item">Erros: <span id="error-count">0</span></div>
            </div>
            
            <div class="textarea-container">
                <textarea id="lista_cartoes" placeholder="Cole sua lista aqui... (CC|MM|AA|CVV)"></textarea>
            </div>
            
            <div class="result-container">
                <div class="result-box">
                    <div class="result-header">
                        <span style="color: var(--live-green);">✅ Aprovadas</span>
                        <div>
                            <button class="btn btn-sm btn-success mr-2" id="copy-lives" title="Copiar"><i class="fas fa-copy"></i></button>
                            <button class="btn btn-sm btn-info" id="export-lives" title="Exportar"><i class="fas fa-download"></i></button>
                        </div>
                    </div>
                    <div id="lives" class="result-item"></div>
                </div>
                <div class="result-box">
                    <div class="result-header">
                        <span style="color: var(--die-red);">❌ Reprovadas</span>
                        <button class="btn btn-sm btn-danger" id="clear-dies" title="Limpar"><i class="fas fa-trash"></i> Limpar</button>
                    </div>
                    <div id="dies" class="result-item"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script type="text/javascript">
        const startAudio = new Audio('assets/ext.mp3');
        const liveAudio = new Audio('assets/cu.mp3');
        const tokenExpiry = new Date('<?php echo $_SESSION['token_data']['expiry']; ?>').getTime();
        
        // VARIÁVEIS FIXAS
        const fixedApi = '<?php echo $fixedApi; ?>';
        const fixedThreads = <?php echo $fixedThreads; ?>;

        $(document).ready(function() {
            
            // Exibir o modal ao carregar a página
            $('#welcomeModal').modal('show');

            // Handler do botão Gerador
            $('#btn-gerador').click(function() {
                window.open('gerador.php', '_blank', 'width=800,height=600').focus();
            });

            let worker = {
                active: false,
                paused: false,
                threads: fixedThreads,
                total: 0,
                tested: 0,
                lives: 0,
                dies: 0,
                errors: 0,
                requests: [],
                currentIndex: 0,
                originalList: [],
                apis: [fixedApi],
                processingCount: 0,
                apiProcessing: {}
            };

            toastr.options = {
                positionClass: "toast-bottom-right",
                progressBar: true,
                timeOut: 3000
            };

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function validateCard(line) {
                const regex = /^\d{15,16}\|\d{1,2}\|\d{2,4}\|\d{3,4}$/;
                return regex.test(line.replace(/\s/g, ''));
            }

            function updateProgress() {
                const progress = worker.total ? (worker.tested / worker.total) * 100 : 0;
                $('#progress-bar').css('width', `${progress}%`).text(`${Math.round(progress)}%`);
                
                // ATUALIZAÇÃO DOS CAMPOS DE STATUS
                $('#total-count').text(worker.total); // Carregadas
                $('#tested-count').text(worker.tested); // Testadas (NOVO)
                $('#live-count').text(worker.lives);
                $('#die-count').text(worker.dies);
                $('#error-count').text(worker.errors);
                
                if (worker.active) {
                    const remaining = worker.originalList.slice(worker.currentIndex).join('\n');
                    $('#lista_cartoes').val(remaining);
                }
            }
            
            function updateTimeRemaining() {
                const now = Date.now();
                const timeLeft = tokenExpiry - now;
                if (timeLeft <= 0) {
                    toastr.error("Seu token expirou!");
                }
            }
            setInterval(updateTimeRemaining, 1000);


            function processLine(index, api) {
                if (!worker.active || worker.paused || index >= worker.originalList.length) return;

                const line = worker.originalList[index];
                worker.processingCount++;
                worker.apiProcessing[api] = (worker.apiProcessing[api] || 0) + 1;

                const ajaxReq = $.ajax({
                    url: api,
                    type: 'GET',
                    data: {
                        lista: line,
                        token_api: $('#token_api').val(),
                    },
                    beforeSend: function() {
                        $('#progress-bar').css('background', 'linear-gradient(90deg, #ffaa00, #ff7700)');
                    },
                    success: function(response) {
                        const resp = response.trim();
                        if (resp.indexOf("Aprovada") >= 0) {
                            worker.lives++;
                            $('#lives').prepend(`<div class="result-item" style="color: var(--live-green);">${resp}</div>`);
                            liveAudio.pause();
                            liveAudio.currentTime = 0;
                            liveAudio.play();
                        } else if (resp.indexOf("Reprovada") >= 0) {
                            worker.dies++;
                            $('#dies').prepend(`<div class="result-item" style="color: var(--die-red);">${resp}</div>`);
                        } else {
                            worker.errors++;
                            $('#dies').prepend(`<div class="result-item" style="color: var(--primary-blue);">${resp}</div>`);
                        }
                    },
                    error: function() {
                        worker.errors++;
                        $('#dies').prepend(`<div class="result-item" style="color: var(--primary-blue);">Erro de conexão: ${line}</div>`);
                    },
                    complete: function() {
                        worker.tested++;
                        worker.processingCount--;
                        worker.apiProcessing[api]--;
                        $('#progress-bar').css('background', 'linear-gradient(90deg, var(--primary-blue), var(--highlight-cyan))');
                        updateProgress();
                        if (!worker.paused && worker.active) processNext();
                    }
                });
                worker.requests.push(ajaxReq);
            }

            function processNext() {
                if (!worker.active || worker.paused || worker.currentIndex >= worker.originalList.length) return;

                while (worker.processingCount < worker.threads && worker.currentIndex < worker.originalList.length) {
                    processLine(worker.currentIndex, fixedApi);
                    worker.currentIndex++;
                }
            }


            $('#chk-start').click(debounce(function() {
                const lista = $('#lista_cartoes').val().trim().split('\n').filter(l => l.trim());
                const invalidLines = lista.filter(line => !validateCard(line));

                if (lista.length === 0) {
                    toastr.error("Insira uma lista válida!");
                    return;
                }
                if (invalidLines.length > 0) {
                    toastr.error(`Linhas inválidas: ${invalidLines.length}`);
                    return;
                }

                worker = {
                    active: true,
                    paused: false,
                    threads: fixedThreads,
                    total: lista.length,
                    tested: 0,
                    lives: 0,
                    dies: 0,
                    errors: 0,
                    requests: [],
                    currentIndex: 0,
                    originalList: lista,
                    apis: [fixedApi],
                    processingCount: 0,
                    apiProcessing: {}
                };

                $('#chk-stop, #chk-pause').prop('disabled', false);
                $('#chk-start').prop('disabled', true);
                $('#lista_cartoes').prop('readonly', true);
                toastr.info(`Checker iniciado com ${fixedThreads} threads na API PAYPAL!`);
                startAudio.play();
                processNext();
            }, 300));

            $('#chk-pause').click(debounce(function() {
                worker.paused = !worker.paused;
                $(this).html(worker.paused ? '<i class="fas fa-play"></i> Continuar' : '<i class="fas fa-pause"></i> Pausar');
                toastr.info(worker.paused ? "Pausado!" : "Retomado!");
                if (!worker.paused) processNext();
            }, 300));

            $('#chk-stop').click(debounce(function() {
                worker.active = false;
                worker.requests.forEach(req => req.abort());
                $('#chk-stop, #chk-pause').prop('disabled', true);
                $('#chk-start').prop('disabled', false);
                $('#lista_cartoes').prop('readonly', false);
                toastr.warning("Checker parado!");
                startAudio.pause();
                liveAudio.pause();
            }, 300));

            $('#chk-clean').click(debounce(function() {
                $('#lista_cartoes').val('').prop('readonly', false);
                $('#lives, #dies').empty();
                worker = {
                    active: false,
                    paused: false,
                    threads: fixedThreads,
                    total: 0,
                    tested: 0,
                    lives: 0,
                    dies: 0,
                    errors: 0,
                    requests: [],
                    currentIndex: 0,
                    originalList: [],
                    apis: [fixedApi],
                    processingCount: 0,
                    apiProcessing: {}
                };
                updateProgress();
                toastr.info("Tudo limpo!");
            }, 300));

            $('#copy-lives').click(function() {
                const livesText = $('#lives').children().map((i, el) => $(el).text()).get().join('\n');
                navigator.clipboard.writeText(livesText);
                toastr.success("Aprovadas copiadas!");
            });

            $('#export-lives').click(function() {
                const livesText = $('#lives').children().map((i, el) => $(el).text()).get().join('\n');
                const blob = new Blob([livesText], { type: 'text' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'aprovadas.txt';
                a.click();
                window.URL.revokeObjectURL(url);
                toastr.success("Aprovadas exportadas!");
            });

            $('#clear-dies').click(function() {
                $('#dies').empty();
                toastr.info("Reprovadas limpas!");
            });
        });
    </script>

</body>

</html>