<?php

class IcsGenerator
{
    public function gerar(array $jogos, array $env): string
    {
        $linhas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SPFC//Brasileirao 2026//PT-BR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Brasileirão 2026 - SPFC',
            'X-WR-TIMEZONE:America/Sao_Paulo',
        ];

        foreach ($jogos as $id => $jogo) {

            [$dia, $mes, $ano] = explode('/', $jogo['data']);
            [$hora, $min] = explode(':', $jogo['hora']);

            $inicio = sprintf(
                '%04d%02d%02dT%02d%02d00',
                $ano,
                $mes,
                $dia,
                $hora,
                $min
            );

            // duração padrão: 2h
            $fim = date('Ymd\THis', strtotime('+2 hours', strtotime(
                "$ano-$mes-$dia $hora:$min"
            )));

            $titulo = sprintf(
                'Rodada %s – %s x %s',
                $jogo['rodada'],
                $jogo['mandante'],
                $jogo['visitante']
            );

            $linhas[] = 'BEGIN:VEVENT';
            $linhas[] = 'UID:' . $id . '@spfc';
            $linhas[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z', strtotime(
                "$ano-$mes-$dia $hora:$min"
            ));

            $linhas[] = 'DTSTART;TZID=America/Sao_Paulo:' . $inicio;
            $linhas[] = 'DTEND;TZID=America/Sao_Paulo:' . $fim;

            $linhas[] = 'SUMMARY:' . $this->escape($titulo);
            $linhas[] = 'LOCATION:' . $this->escape($jogo['local']);

            // alerta 1 dia antes
            $linhas[] = 'BEGIN:VALARM';
            $linhas[] = 'TRIGGER:-PT24H';
            $linhas[] = 'ACTION:DISPLAY';
            $linhas[] = 'DESCRIPTION:Lembrete do jogo do São Paulo';
            $linhas[] = 'END:VALARM';

            $linhas[] = 'END:VEVENT';
        }

        $linhas[] = 'END:VCALENDAR';

        return implode("\r\n", $linhas);
    }

    private function escape(string $texto): string
    {
        return addcslashes($texto, ",;\\");
    }
}
