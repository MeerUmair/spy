<?php
	$v1 = (isset($_GET['v1']) && is_numeric($_GET['v1']))? $_GET['v1']:7;
	$v2 = (isset($_GET['v2']) && is_numeric($_GET['v2']))? $_GET['v2']:7;
	$v3 = (isset($_GET['v3']) && is_numeric($_GET['v3']))? $_GET['v3']:7;
	$v4 = (isset($_GET['v4']) && is_numeric($_GET['v4']))? $_GET['v4']:7;
	$v5 = (isset($_GET['v5']) && is_numeric($_GET['v5']))? $_GET['v5']:7;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <head>
            <script src="jquery-1.9.0.min.js"></script>
            <script src="Chart.js"></script>
            <style>
                
            </style>
        </head>
        <body>
            <div class="wrap">
                <canvas id="canvas3" width="300" height="175"></canvas>
            </div>
            <script>
                $(function () {
                    // チャートの枠組み
                    var radarChartData = {
                        // 項目
                        labels: ["総合おすすめ点", "体感画質", "暗視性能", "隠ぺい力", "扱い易さ"],
                        datasets: [
                            {
                                // 透明を使いたいのでRGBAで色を再現→rgba(xxx,xxx,xxx,0.5):透過度50%
                                fillColor: "rgba(0,153,255,0.7)", // チャート内の色
                                strokeColor: "#111111", // チャートを囲む線の色
                                pointColor: "#111111", // チャートの点の色
                                pointStrokeColor: "#fff", // 点を囲む線の色
                                // 各項目の値
                                data: [<?php echo $v1; ?>, <?php echo $v2; ?>, <?php echo $v3; ?>, <?php echo $v4; ?>, <?php echo $v5; ?>]
                            }
                        ]
                    };
                    // レーダーチャートの目盛とかの設定
                    var canvas3 = document.getElementById("canvas3");
                    var context3 = canvas3.getContext("2d");
                    var chart = new Chart(context3);
                    chart.Radar(radarChartData, {
                        scaleShowLabels: true, // 目盛を表示（true/false）
                        pointLabelFontSize: 12, // ラベルのフォントサイズ
                        scaleOverride: true, // 目盛の最大値を手動設定（true/false）
                        scaleSteps: 5, // 目盛の数
                        scaleStartValue: 5, // 目盛の最初の数
                        scaleStepWidth: 1 // 目盛の間隔
                                // 目盛の最大値の計算：scaleSteps（目盛の数）→5　scaleStepWidth（目盛の間隔）→2 だと5×2で最大値は10
                    });
                });

            </script>

        </body>
</html>