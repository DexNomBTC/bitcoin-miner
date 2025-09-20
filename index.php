<?php
require_once __DIR__ . '/config.php';
function read_json($path) {
    if (file_exists($path)) {
        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
    return [];
}

$status  = read_json(STATUS_FILE);
$blocks  = read_json(BLOCKS_FILE);
$workers = read_json(WORKERS_FILE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= POOL_NAME ?> — Dashboard</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <header class="header">
    <div class="brand">
      <div class="logo">⛏️</div>
      <div>
        <h1><?= POOL_NAME ?></h1>
        <p class="subtitle">Mock dashboard for the fictional <strong>Bincoin (<?= COIN_TICKER ?>)</strong> mining pool</p>
      </div>
    </div>
    <nav class="nav">
      <a href="./">Dashboard</a>
      <a href="api/status.php" target="_blank">API: Status</a>
      <a href="api/blocks.php" target="_blank">API: Blocks</a>
      <a href="api/workers.php" target="_blank">API: Workers</a>
      <a href="README.md" target="_blank">README</a>
    </nav>
  </header>

  <main class="container">
    <section class="cards">
      <div class="card">
        <h3>Network Hashrate</h3>
        <div class="metric" id="networkHashrate"><?= htmlspecialchars($status['network_hashrate_hs'] ?? 0) ?></div>
        <div class="muted">H/s</div>
      </div>
      <div class="card">
        <h3>Pool Hashrate</h3>
        <div class="metric" id="poolHashrate"><?= htmlspecialchars($status['pool_hashrate_hs'] ?? 0) ?></div>
        <div class="muted">H/s</div>
      </div>
      <div class="card">
        <h3>Miners Online</h3>
        <div class="metric" id="minersOnline"><?= htmlspecialchars($status['miners_online'] ?? 0) ?></div>
      </div>
      <div class="card">
        <h3>Last Block</h3>
        <div class="metric" id="lastBlock"><?= htmlspecialchars($status['last_block_height'] ?? '-') ?></div>
      </div>
      <div class="card">
        <h3>Difficulty</h3>
        <div class="metric" id="difficulty"><?= htmlspecialchars($status['difficulty'] ?? '-') ?></div>
      </div>
      <div class="card">
        <h3>24h Blocks</h3>
        <div class="metric" id="blocks24h"><?= htmlspecialchars($status['blocks_24h'] ?? 0) ?></div>
      </div>
    </section>

    <section class="panel">
      <h2>Recent Blocks</h2>
      <table>
        <thead>
          <tr>
            <th>Height</th>
            <th>Hash</th>
            <th>Reward (<?= COIN_TICKER ?>)</th>
            <th>Time (UTC)</th>
            <th>Miner</th>
          </tr>
        </thead>
        <tbody id="blocksTable">
          <?php foreach (($blocks ?? []) as $b): ?>
            <tr>
              <td><?= htmlspecialchars($b['height']) ?></td>
              <td class="hash"><?= htmlspecialchars($b['hash']) ?></td>
              <td><?= htmlspecialchars($b['reward_bnc']) ?></td>
              <td><?= htmlspecialchars($b['time_utc']) ?></td>
              <td><?= htmlspecialchars($b['miner']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <section class="panel">
      <h2>Your Workers (Sample)</h2>
      <table>
        <thead>
          <tr>
            <th>Worker</th>
            <th>Hashrate (H/s)</th>
            <th>Shares (valid/invalid)</th>
            <th>Uptime</th>
          </tr>
        </thead>
        <tbody id="workersTable">
          <?php foreach (($workers ?? []) as $w): ?>
            <tr>
              <td><?= htmlspecialchars($w['name']) ?></td>
              <td><?= htmlspecialchars($w['hashrate_hs']) ?></td>
              <td><?= htmlspecialchars($w['shares_valid']) ?>/<?= htmlspecialchars($w['shares_invalid']) ?></td>
              <td><?= htmlspecialchars($w['uptime']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

  <footer class="footer">2025 © Bincoin Mining Pool — demo only</footer>

  <script>
    async function refresh() {
      try {
        const [status, blocks, workers] = await Promise.all([
          fetch('api/status.php').then(r => r.json()),
          fetch('api/blocks.php').then(r => r.json()),
          fetch('api/workers.php').then(r => r.json()),
        ]);
        document.getElementById('networkHashrate').textContent = status.network_hashrate_hs ?? 0;
        document.getElementById('poolHashrate').textContent    = status.pool_hashrate_hs ?? 0;
        document.getElementById('minersOnline').textContent    = status.miners_online ?? 0;
        document.getElementById('lastBlock').textContent       = status.last_block_height ?? '-';
        document.getElementById('difficulty').textContent      = status.difficulty ?? '-';
        document.getElementById('blocks24h').textContent       = status.blocks_24h ?? 0;

        // Blocks
        const bt = document.getElementById('blocksTable');
        bt.innerHTML = '';
        (blocks || []).forEach(b => {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${b.height}</td>
                          <td class="hash">${b.hash}</td>
                          <td>${b.reward_bnc}</td>
                          <td>${b.time_utc}</td>
                          <td>${b.miner}</td>`;
          bt.appendChild(tr);
        });

        // Workers
        const wt = document.getElementById('workersTable');
        wt.innerHTML = '';
        (workers || []).forEach(w => {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${w.name}</td>
                          <td>${w.hashrate_hs}</td>
                          <td>${w.shares_valid}/${w.shares_invalid}</td>
                          <td>${w.uptime}</td>`;
          wt.appendChild(tr);
        });
      } catch (e) {
        console.error(e);
      }
    }
    setInterval(refresh, (<?= REFRESH_SECONDS ?>) * 1000);
  </script>
</body>
</html>
