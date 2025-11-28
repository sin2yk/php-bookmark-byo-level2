<?php
require_once 'db_connect.php';
$pdo = get_pdo();

$sql = 'SELECT * FROM bottle_entries WHERE event_id = 1 ORDER BY created_at ASC, id ASC';
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// タイプラベル
$wineTypeLabel = [
    'sparkling' => '泡',
    'white'     => '白',
    'orange'    => 'オレンジ',
    'red_pinot' => '赤（Pinot）',
    'red_other' => '赤（その他）',
    'rose'      => 'ロゼ',
    'sweet'     => '甘口',
    'fortified' => '酒精強化',
];

// 価格帯ラベル（幹事用：レンジ＋呼び名）
$priceLabelHost = [
    'charming'  => '〜5千円「Charming」',
    'reasonable'=> '5〜10千円「Reasonable」',
    'valuable'  => '10〜20千円「Valuable」',
    'expensive' => '20〜50千円「Expensive」',
    'prestige'  => '50千円以上「Prestige」',
];

// テーマ適合度ラベル
$themeLabel = [
    5 => '5：完璧に合致',
    4 => '4：ほぼ合致',
    3 => '3：まあまあ合う',
    2 => '2：ややズレている',
    1 => '1：テーマ外',
];

// サマリー用集計
$typeCount  = [];
$priceCount = [];
$themeSum   = 0;
$themeN     = 0;

foreach ($rows as $r) {
    $tKey = $r['wine_type'];
    if ($tKey !== '') {
        $typeCount[$tKey] = ($typeCount[$tKey] ?? 0) + 1;
    }

    $pKey = $r['price_band'];
    if ($pKey !== '') {
        $priceCount[$pKey] = ($priceCount[$pKey] ?? 0) + 1;
    }

    if ($r['theme_fit'] !== null && $r['theme_fit'] !== '') {
        $themeSum += (int)$r['theme_fit'];
        $themeN++;
    }
}

$themeAvg = $themeN > 0 ? round($themeSum / $themeN, 2) : null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>BYO：幹事用サマリー</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #faf5f0;
      color: #333;
    }
    a { color: #8b4b2b; text-decoration: none; }
    a:hover { text-decoration: underline; }
    header {
      background: #3b2b2b;
      color: #f5f5f5;
      padding: 16px 20px;
    }
    header h1 { margin: 0; font-size: 22px; }
    main {
      max-width: 1000px;
      margin: 24px auto 40px;
      padding: 0 16px;
    }
    .back-link {
      display: inline-block;
      margin-bottom: 8px;
      font-size: 13px;
    }
    .card {
      background: #fff;
      border-radius: 8px;
      padding: 16px 20px 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.06);
      margin-bottom: 16px;
    }
    h2 {
      margin-top: 0;
      font-size: 18px;
      border-left: 4px solid #c48b5a;
      padding-left: 8px;
    }
    ul {
      margin: 4px 0 8px 20px;
      padding: 0;
      font-size: 14px;
    }
    .sheet {
      background: #fff;
      border-radius: 12px;
      padding: 24px 24px 28px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      border: 1px solid #e2d3c5;
    }
    .sheet-title {
      text-align: center;
      margin-bottom: 8px;
      font-size: 22px;
      letter-spacing: 0.08em;
    }
    .sheet-subtitle {
      text-align: center;
      font-size: 13px;
      color: #666;
      margin-bottom: 20px;
    }
    .wine-item {
      margin-bottom: 10px;
      padding-bottom: 8px;
      border-bottom: 1px dashed #e0d4c9;
    }
    .wine-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    .wine-line1 {
      font-weight: 600;
      font-size: 14px;
    }
    .wine-line2 {
      font-size: 13px;
      color: #555;
      margin-top: 2px;
    }
    .wine-line3 {
      font-size: 12px;
      color: #666;
      margin-top: 2px;
    }
  </style>
</head>
<body>
  <header>
    <h1>BYO：幹事用サマリー</h1>
  </header>
  <main>
    <a href="index.php" class="back-link">← トップに戻る</a>

    <!-- サマリー -->
    <section class="card">
      <h2>サマリー</h2>

      <h3 style="font-size:15px;">タイプ別本数</h3>
      <ul>
        <?php if (empty($typeCount)): ?>
          <li>登録なし</li>
        <?php else: ?>
          <?php foreach ($typeCount as $key => $cnt): ?>
            <li><?php echo htmlspecialchars(($wineTypeLabel[$key] ?? $key) . '：' . $cnt . '本', ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>

      <h3 style="font-size:15px;">価格帯別本数</h3>
      <ul>
        <?php if (empty($priceCount)): ?>
          <li>登録なし</li>
        <?php else: ?>
          <?php foreach ($priceCount as $key => $cnt): ?>
            <li><?php echo htmlspecialchars(($priceLabelHost[$key] ?? $key) . '：' . $cnt . '本', ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>

      <h3 style="font-size:15px;">テーマ適合度 平均</h3>
      <p style="margin:4px 0 0 0; font-size:14px;">
        <?php if ($themeAvg === null): ?>
          まだデータがありません。
        <?php else: ?>
          <?php echo htmlspecialchars($themeAvg . '（1〜5）', ENT_QUOTES, 'UTF-8'); ?>
        <?php endif; ?>
      </p>

      <h3 style="font-size:15px; margin-top:12px;">メモ</h3>
      <p style="font-size:13px; color:#666; margin:4px 0 0 0;">
        泡／白／赤のバランスや価格帯を見て、必要に応じて参加者に調整依頼をしてください。<br>
        ブラインド指定された項目は、幹事ビューでも ??? / XXXX などで伏せています。
      </p>
    </section>

    <!-- 幹事用ワインリスト -->
    <section class="sheet">
      <div class="sheet-title">Wine List（幹事用）</div>
      <div class="sheet-subtitle">
        2025-12-23  Ăn Đi 神宮前 / internal use only
      </div>

      <?php if (count($rows) === 0): ?>
        <p style="text-align:center; font-size:14px; color:#666;">まだ登録されたワインはありません。</p>
      <?php else: ?>
        <?php $no = 1; ?>
        <?php foreach ($rows as $row): ?>
          <?php
            // 幹事ビュー：登録者／タイプ／テーマ適合度は常に見える
            $participant = $row['participant_name'];
            $typeKey     = $row['wine_type'];
            $typeStr     = $wineTypeLabel[$typeKey] ?? $typeKey;

            $themeVal    = (int)$row['theme_fit'];
            $themeStr    = $themeLabel[$themeVal] ?? '';

            // それ以外はブラインドフラグに従って伏せる
            $producer = $row['blind_producer'] ? '???' : $row['wine_producer'];
            $wineName = $row['blind_wine_name'] ? '???' : $row['wine_name'];
            $vintage  = $row['blind_vintage'] ? 'XXXX' : $row['wine_vintage'];
            $region   = $row['blind_region'] ? '???' : $row['region'];

            $priceKey = $row['price_band'];
            if ($row['blind_price_band']) {
                $priceStr = '（非公開）';
            } else {
                $priceStr = $priceLabelHost[$priceKey] ?? '';
            }

            if ($row['blind_comment']) {
                $commentStr = '（ブラインド指定のため非表示）';
            } else {
                $commentStr = $row['comment'];
            }

            $titleParts = [];
            if ($producer !== '') $titleParts[] = $producer;
            if ($wineName !== '') $titleParts[] = $wineName;
            if ($vintage !== '')  $titleParts[] = $vintage;
            $line1 = $no . '. ' . (count($titleParts) ? implode(' – ', $titleParts) : '（未入力）');

            $line2Parts = [];
            if ($region !== '') $line2Parts[] = $region;
            if ($typeStr !== '') $line2Parts[] = 'Type: ' . $typeStr;
            if ($priceStr !== '') $line2Parts[] = 'Price: ' . $priceStr;
            $line2 = count($line2Parts) ? implode(' / ', $line2Parts) : '';

            $line3Parts = [];
            if ($participant !== '') $line3Parts[] = '登録者：' . $participant;
            if ($themeStr !== '')   $line3Parts[] = 'テーマ適合度：' . $themeStr;
            $line3 = count($line3Parts) ? implode(' / ', $line3Parts) : '';
          ?>
          <div class="wine-item">
            <div class="wine-line1"><?php echo htmlspecialchars($line1, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php if ($line2 !== ''): ?>
              <div class="wine-line2"><?php echo htmlspecialchars($line2, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($line3 !== ''): ?>
              <div class="wine-line3"><?php echo htmlspecialchars($line3, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($commentStr !== ''): ?>
              <div class="wine-line3">
                メモ：<?php echo nl2br(htmlspecialchars($commentStr, ENT_QUOTES, 'UTF-8')); ?>
              </div>
            <?php endif; ?>
          </div>
          <?php $no++; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
