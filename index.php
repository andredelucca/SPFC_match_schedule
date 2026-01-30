<?php

require 'src/CbfClient.php';
require 'src/IcsGenerator.php';

$env = parse_ini_file('.env');

$client = new CbfClient();
$payload = $client->jogosCampeonato((int) $env['CAMPEONATO_ID']);

$jogos = $payload['jogos'] ?? [];

$spfcJogos = array_filter($jogos, function ($jogo) use ($env) {
    return
        $jogo['mandante']['id'] === $env['TIME_ID'] ||
        $jogo['visitante']['id'] === $env['TIME_ID'];
});

$cacheFile = __DIR__ . '/storage/jogos_spfc.json';

$cache = [];
if (file_exists($cacheFile)) {
    $cache = json_decode(file_get_contents($cacheFile), true) ?? [];
}

$atualizado = false;
$novosEventos = [];

foreach ($spfcJogos as $jogo) {

    // ignora jogos sem data
    if (empty(trim($jogo['data'] ?? ''))) {
        continue;
    }

    $idJogo = $jogo['id_jogo'];

    $hashAtual = md5(
        $jogo['data'] .
        $jogo['hora'] .
        $jogo['local']
    );

    $hashCache = $cache[$idJogo]['hash'] ?? null;

    // jogo novo OU alterado
    if ($hashAtual !== $hashCache) {

        $cache[$idJogo] = [
            'hash'   => $hashAtual,
            'rodada' => $jogo['rodada'],
            'data'   => trim($jogo['data']),
            'hora'   => $jogo['hora'],
            'local'  => $jogo['local'],
            'mandante'  => $jogo['mandante']['nome'],
            'visitante' => $jogo['visitante']['nome'],
        ];

        $novosEventos[] = $jogo;
        $atualizado = true;
    }
}

if ($atualizado === false) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Nenhuma atualização encontrada.";
    exit;
}

file_put_contents(
    $cacheFile,
    json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// gera calendário COMPLETO a partir do cache
$eventosParaCalendario = array_values($cache);

$ics = new IcsGenerator();
$conteudo = $ics->gerar($eventosParaCalendario, $env);

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="spfc-brasileirao-2026.ics"');

echo $conteudo;
exit;
