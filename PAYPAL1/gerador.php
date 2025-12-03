<?php
class GenCard {
    private static string $extra;
    private static int $mm;
    private static int $yy;
    private static string $cvv;

    public static function Gen($extra, $mm = '', $yy = '', $cvv = '', $amo = 10) : array {
        self::$extra = $extra;
        self::$mm = intval($mm);
        self::$yy = intval($yy);
        self::$cvv = preg_replace('/\D/', '', $cvv);

        $amo = max(1, min(1000, intval($amo)));

        $cards = [];
        $gcards = [];

        while (count($cards) < $amo) {
            $card = self::GenCard();
            $cardStr = implode('|', $card);
            if (!in_array($card[0], $gcards)) {
                $gcards[] = $card[0];
                $cards[] = $cardStr;
            }
        }

        return $cards;
    }

    private static function GenCard() : array {
        return [self::GenCC(), self::GenMM(), self::GenYY(), self::GenCVV()];
    }

    private static function GenCC() : string {
        $isAmex = (substr(self::$extra, 0, 2) === '37' || substr(self::$extra, 0, 2) === '34');
        $length = $isAmex ? 15 : 16;
        $ccbin = preg_replace('/[^0-9]/', '', substr(self::$extra, 0, $length - 1));
        return self::GenNum($ccbin, $length);
    }

    private static function GenNum($prefix, $length) : string {
        $ccnumber = $prefix;
        while (strlen($ccnumber) < ($length - 1)) {
            $ccnumber .= mt_rand(0, 9);
        }
        $sum = 0;
        $pos = 0;
        $reversedCCnumber = strrev($ccnumber);
        while ($pos < $length - 1) {
            $odd = $reversedCCnumber[$pos] * 2;
            if ($odd > 9) $odd -= 9;
            $sum += $odd;
            if ($pos != ($length - 2)) $sum += $reversedCCnumber[$pos + 1];
            $pos += 2;
        }
        $checkdigit = ((floor($sum / 10) + 1) * 10 - $sum) % 10;
        $ccnumber .= $checkdigit;
        return $ccnumber;
    }

    private static function GenMM() : string {
        return sprintf('%02d', (empty(self::$mm) || self::$mm < 1 || self::$mm > 12 ? mt_rand(1, 12) : self::$mm));
    }

    private static function GenYY() : string {
        $minYear = 2025;
        $maxYear = 2038;
        return (empty(self::$yy) || self::$yy < $minYear || self::$yy > $maxYear ? mt_rand($minYear, $maxYear) : self::$yy);
    }

    private static function GenCVV() : string {
        $isAmex = (substr(self::$extra, 0, 2) === '37' || substr(self::$extra, 0, 2) === '34');
        if ($isAmex) {
            return empty(self::$cvv) || strlen(self::$cvv) != 4 ? sprintf('%04d', mt_rand(1000, 9999)) : self::$cvv;
        }
        return empty(self::$cvv) || strlen(self::$cvv) != 3 ? sprintf('%03d', mt_rand(100, 999)) : self::$cvv;
    }
}

if (isset($_GET['bin']) && isset($_GET['amo'])) {
    $bin = $_GET['bin'];
    $mm = $_GET['mm'] ?? '';
    $yy = $_GET['yy'] ?? '';
    $cvv = $_GET['cvv'] ?? '';
    $amo = $_GET['amo'];
    $cards = GenCard::Gen($bin, $mm, $yy, $cvv, $amo);
    echo implode("\n", $cards);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Gerador de CC - CHK MULTI</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
            background: linear-gradient(135deg, #0a0c1b 0%, #1e1e3c 100%);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            min-height: 100vh;
            margin: 0;
            padding: 30px;
            overflow-x: hidden;
        }
        .card {
            background: rgba(20, 22, 48, 0.95);
            border-radius: 20px;
            padding: 30px;
            border: 2px solid #1e90ff;
            box-shadow: 0 0 30px rgba(30, 144, 255, 0.4);
            max-width: 1200px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 0 40px rgba(30, 144, 255, 0.5);
        }
        .form-control, .form-select {
            background: #0a0c1b;
            color: #fff;
            border: 2px solid #1e90ff;
            border-radius: 12px;
            padding: 10px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background: #0a0c1b;
            color: #fff;
            border-color: #ff00ff;
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
        }
        textarea {
            width: 100%;
            min-height: 300px;
            background: #0a0c1b;
            color: #fff;
            border: 2px solid #1e90ff;
            border-radius: 12px;
            padding: 15px;
            resize: vertical;
            transition: all 0.3s ease;
        }
        textarea:focus {
            border-color: #ff00ff;
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
        }
        .btn {
            border-radius: 12px;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(30, 144, 255, 0.6);
        }
        .btn-success {
            background: linear-gradient(45deg, #00ff7f, #00cc66);
        }
        .btn-info {
            background: linear-gradient(45deg, #1e90ff, #ff00ff);
        }
        .result-box {
            background: rgba(10, 12, 27, 0.98);
            border: 2px solid #1e90ff;
            border-radius: 12px;
            padding: 20px;
            max-height: 350px;
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        .result-box:hover {
            border-color: #ff00ff;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.4);
        }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .result-item {
            font-family: monospace;
            font-size: 13px;
            padding: 8px 0;
            color: #00ff7f;
            transition: all 0.3s ease;
        }
        .result-item:hover {
            background: rgba(30, 144, 255, 0.1);
            padding-left: 5px;
        }
        .menu-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            font-size: 28px;
            color: #1e90ff;
            cursor: pointer;
            z-index: 1001;
            transition: all 0.3s ease;
        }
        .menu-btn:hover {
            color: #ff00ff;
            transform: scale(1.1);
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100%;
            background: rgba(10, 12, 27, 0.98);
            border-right: 2px solid #1e90ff;
            padding: 30px 20px;
            box-shadow: 0 0 30px rgba(30, 144, 255, 0.3);
            z-index: 1000;
            transition: left 0.4s ease;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 15px;
            color: #1e90ff;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            font-weight: 500;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background: linear-gradient(45deg, rgba(30, 144, 255, 0.2), rgba(255, 0, 255, 0.2));
            color: #fff;
            transform: translateX(5px);
        }
        .sidebar-item i {
            margin-right: 12px;
            font-size: 18px;
        }
        @media (max-width: 991px) {
            .menu-btn {
                display: block;
            }
            .row {
                flex-direction: column;
            }
            .col-md-8, .col-md-4, .col-md-3, .col-md-2, .col-md-1 {
                width: 100%;
                margin-bottom: 15px;
            }
        }
        @media (min-width: 992px) {
            .menu-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="menu-btn" id="menu-btn"><i class="fas fa-bars"></i></div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-item" id="index-btn"><i class="fas fa-arrow-left"></i> Voltar à Index</div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" id="bin" class="form-control" placeholder="BIN/Matriz (ex: 510510)">
                </div>
                <div class="col-md-2">
                    <select id="mm" class="form-select">
                        <option value="">Mês Aleatório</option>
                        <?php for ($i = 1; $i <= 12; $i++) echo "<option value='$i'>" . sprintf('%02d', $i) . "</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="yy" class="form-select">
                        <option value="">Ano Aleatório</option>
                        <?php for ($i = 2025; $i <= 2038; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" id="cvv" class="form-control" placeholder="CVV (3 ou 4, ou vazio)">
                </div>
                <div class="col-md-2">
                    <input type="number" id="amo" class="form-control" placeholder="Quantidade (1-1000)" min="1" max="1000" value="10">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success w-100" id="generate"><i class="fas fa-play"></i></button>
                </div>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-8">
                    <textarea id="generated-cards" placeholder="Cartões gerados aparecerão aqui..."></textarea>
                </div>
                <div class="col-md-4">
                    <div class="result-box">
                        <div class="result-header">
                            <span style="color: #00ff7f;">Gerados</span>
                            <div>
                                <button class="btn btn-sm btn-success mr-2" id="copy-generated" title="Copiar">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-sm btn-info" id="use-generated" title="Usar na Index">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                            </div>
                        </div>
                        <div id="generated-list" class="result-item"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            toastr.options = {
                positionClass: "toast-bottom-right",
                progressBar: true,
                timeOut: 3000,
                preventDuplicates: true
            };

            $('#generate').click(function() {
                const bin = $('#bin').val().trim();
                const mm = $('#mm').val();
                const yy = $('#yy').val();
                const cvv = $('#cvv').val().trim();
                const amo = $('#amo').val() || 10;

                if (!bin || bin.length < 6) {
                    toastr.error("Insira uma BIN/Matriz válida (mínimo 6 dígitos)!");
                    return;
                }

                $.ajax({
                    url: 'gerador.php',
                    type: 'GET',
                    data: { bin, mm, yy, cvv, amo },
                    success: function(response) {
                        const cards = response.trim().split('\n');
                        $('#generated-cards').val(response);
                        $('#generated-list').empty();
                        cards.forEach(card => {
                            $('#generated-list').append(`<div class="result-item">${card}</div>`);
                        });
                        toastr.success(`Gerados ${cards.length} cartões!`);
                    },
                    error: function() {
                        toastr.error("Erro ao gerar cartões!");
                    }
                });
            });

            $('#copy-generated').click(function() {
                const text = $('#generated-cards').val();
                navigator.clipboard.writeText(text);
                toastr.success("Cartões copiados!");
            });

            $('#use-generated').click(function() {
                const text = $('#generated-cards').val();
                if (window.opener && !window.opener.closed) {
                    window.opener.$('#lista_cartoes').val(text);
                    toastr.success("Cartões enviados para a Index!");
                } else {
                    toastr.error("Index não encontrada! Abra o gerador a partir da Index.");
                }
            });

            $('#menu-btn').click(function() {
                $('#sidebar').toggleClass('active');
            });

            $('#index-btn').click(function() {
                if (window.opener && !window.opener.closed) {
                    window.opener.focus();
                    window.close();
                } else {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
</body>
</html>