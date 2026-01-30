<?php

class CbfClient
{
    private string $baseUrl = 'https://www.cbf.com.br/api/proxy';

    public function jogosCampeonato(int $campeonatoId): array
    {
        $url = $this->baseUrl . '?path=/jogos/campeonato/' . $campeonatoId;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FAILONERROR => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
