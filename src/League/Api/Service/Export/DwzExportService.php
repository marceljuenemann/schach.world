<?php

namespace Nsv\League\Api\Service\Export;

use DOMDocument;
use DOMElement;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Player;

// Private implementation detail — not part of the public API of this module.
// PHP does not support inner classes; this lives here to avoid a separate file.
readonly class GameData
{
    public function __construct(
        public int $round,
        public ?string $date,
        public ?Player $white,
        public ?Player $black,
        public string $pointWhite,
        public string $pointBlack,
    ) {}
}

class DwzExportService
{
    /**
     * Generates a DSB_DWZ_TournamentReport 2.5 XML string for the given divisions.
     * All divisions must belong to the given league.
     *
     * @param Division[] $divisions
     */
    public function generateXml(League $league, array $divisions): string
    {
        foreach ($divisions as $division) {
            if ($division->league->id !== $league->id) {
                throw new \InvalidArgumentException(
                    "Division {$division->id} does not belong to league {$league->id}."
                );
            }
        }

        $games = $this->collectGames($divisions);

        // Collect unique players in encounter order
        $players = [];
        foreach ($games as $game) {
            foreach ([$game->white, $game->black] as $player) {
                if ($player !== null && !isset($players[$player->id])) {
                    $players[$player->id] = $player;
                }
            }
        }

        // Assign sequential player numbers (1-indexed)
        $playerNumbers = [];
        $i = 1;
        foreach ($players as $id => $_) {
            $playerNumbers[$id] = $i++;
        }

        $allDates = array_values(array_filter(array_map(fn($g) => $g->date, $games)));
        $startDate = $allDates ? min($allDates) : date('Y-m-d');
        $endDate   = $allDates ? max($allDates) : date('Y-m-d');
        $rounds    = max(array_map(fn($g) => $g->round, $games));

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('DSB_DWZ_TournamentReport');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xmlns', 'http://www.schachbund.de/XMLSchema');
        $root->setAttribute('xsi:schemaLocation',
            'http://www.schachbund.de/XMLSchema ' .
            'https://www.schachbund.de/files/wertungsportal/XMLSchema/2026/DSB_DWZ_Tounament_2_5.xsd'
        );
        $root->setAttribute('version', '2.5');
        $dom->appendChild($root);

        $root->appendChild($this->buildHeader($dom));
        $root->appendChild($this->buildTournament($dom, $league, $rounds, $startDate, $endDate));
        $root->appendChild($this->buildPlayers($dom, $players));
        $root->appendChild($this->buildGames($dom, $games, $playerNumbers));

        $xml = $dom->saveXML();
        $this->validateXml($xml);
        return $xml;
    }

    private function validateXml(string $xml): void
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        libxml_use_internal_errors(true);
        $valid = $dom->schemaValidate(__DIR__ . '/DSB_DWZ_Tounament_2_5_1.xsd');
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$valid) {
            $messages = array_map(fn(\LibXMLError $e) => trim($e->message) . " (line {$e->line})", $errors);
            throw new \RuntimeException("Generated XML failed XSD validation:\n" . implode("\n", $messages));
        }
    }

    /** @param Division[] $divisions */
    private function collectGames(array $divisions): array
    {
        $games = [];
        foreach ($divisions as $division) {
            $dates = $division->dates();
            foreach ($division->pairings as $pairing) {
                $date = $pairing->moveDate() ?? ($dates[$pairing->round]->date ?? null);
                foreach ($pairing->games as $game) {
                    if ($game->result1 === Result::UNKNOWN || $game->result2 === Result::UNKNOWN) {
                        continue;
                    }
                    if ($game->player1 === null && $game->player2 === null) {
                        continue;
                    }
                    $games[] = new GameData(
                        round: $pairing->round,
                        date: $date,
                        white: $game->player1,
                        black: $game->player2,
                        pointWhite: $this->toXmlScore($game->result1),
                        pointBlack: $this->toXmlScore($game->result2),
                    );
                }
            }
        }
        return $games;
    }

    private function buildHeader(DOMDocument $dom): DOMElement
    {
        $header = $dom->createElement('header');
        $this->el($dom, $header, 'creationDate', (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM));
        $this->el($dom, $header, 'sender', 'Norddeutscher Schachverband');  // TODO: configurable
        $this->el($dom, $header, 'system', 'schach.world');
        $this->el($dom, $header, 'fileUUID', $this->randomUuid());
        return $header;
    }

    private function buildTournament(
        DOMDocument $dom,
        League $league,
        int $rounds,
        string $startDate,
        string $endDate,
    ): DOMElement {
        $t = $dom->createElement('tournament');
        $this->el($dom, $t, 'tournamentUUID', $this->stableUuid("nsv-league-{$league->id}"));  // TODO: Generate based on division IDs
        $this->el($dom, $t, 'label', $league->name);  // TODO: configurable
        $this->el($dom, $t, 'tournamentType', 'TR');  // Team Tournament, Round Robin.
        $this->el($dom, $t, 'timecontrol', '90min für 40 Züge + 30min für Rest der Partie, 30s Aufschlag'); // TODO: configurable
        $this->el($dom, $t, 'rounds', (string) $rounds);
        $this->el($dom, $t, 'startDate', $startDate);
        $this->el($dom, $t, 'endDate', $endDate);
        $this->el($dom, $t, 'location', 'Norddeutscher Schachverband'); // TODO: configurable
        $this->el($dom, $t, 'url', $league->uriWithHostAndScheme());
        $this->el($dom, $t, 'ageClass', 'U20'); // TODO: configurable
        return $t;
    }

    private function buildPlayers(DOMDocument $dom, array $players): DOMElement
    {
        $playersEl = $dom->createElement('players');
        $number = 1;
        foreach ($players as $player) {
            $playersEl->appendChild($this->buildPlayer($dom, $player, $number++));
        }
        return $playersEl;
    }

    private function buildPlayer(DOMDocument $dom, Player $player, int $number): DOMElement
    {
        $el = $dom->createElement('player');
        $this->el($dom, $el, 'tournamentPlayerNumber', (string) $number);
        $this->el($dom, $el, 'surname', trim($player->lastName));
        $this->el($dom, $el, 'forename', trim($player->firstName));
        // TODO: dobYear is required by the XSD; 1900 is used as a placeholder when the birth
        // year is not stored. The spec reserves 1900 for chess computers, so ideally this
        // should be blocked at data-entry time.
        // TODO: Error if DOB missing
        $this->el($dom, $el, 'dobYear', (string) ($player->yearOfBirth() ?? 1900));

        [$vkz, $memberNr] = $this->parseZps($player->zps);
        if ($vkz !== '') {
            $this->el($dom, $el, 'vkz', $vkz);
            $memberNrInt = (int) $memberNr;
            if ($memberNrInt >= 1 && $memberNrInt <= 99999) {
                $this->el($dom, $el, 'numberClubMember', (string) $memberNrInt);
            }
        }
        $this->el($dom, $el, 'club', Encoding::utf8_encode($player->team->nameWithNumber()));

        if ($player->elo !== null && $player->elo >= 1 && $player->elo <= 5000) {
            $this->el($dom, $el, 'fideRating', (string) $player->elo);
        }

        if ($player->gender === Player::GENDER_MALE || $player->gender === Player::GENDER_FEMALE) {
            $this->el($dom, $el, 'sex', $player->gender);
        }

        return $el;
    }

    private function buildGames(DOMDocument $dom, array $games, array $playerNumbers): DOMElement
    {
        $gamesEl = $dom->createElement('games');
        foreach ($games as $game) {
            $gamesEl->appendChild($this->buildGame($dom, $game, $playerNumbers));
        }
        return $gamesEl;
    }

    private function buildGame(DOMDocument $dom, GameData $game, array $playerNumbers): DOMElement
    {
        $el = $dom->createElement('game');
        $this->el($dom, $el, 'round', (string) $game->round);
        if ($game->date !== null) {
            $this->el($dom, $el, 'date', $game->date);
        }

        if ($game->white !== null) {
            $this->el($dom, $el, 'tournamentPlayerNumberWhite', (string) $playerNumbers[$game->white->id]);
        } else {
            $noneEl = $dom->createElement('noneWhitePlayer');
            $noneEl->appendChild($dom->createTextNode('true'));
            $el->appendChild($noneEl);
        }

        if ($game->black !== null) {
            $this->el($dom, $el, 'tournamentPlayerNumberBlack', (string) $playerNumbers[$game->black->id]);
        } else {
            $noneEl = $dom->createElement('noneBlackPlayer');
            $noneEl->appendChild($dom->createTextNode('true'));
            $el->appendChild($noneEl);
        }

        $this->el($dom, $el, 'pointWhite', $game->pointWhite);
        $this->el($dom, $el, 'pointBlack', $game->pointBlack);
        return $el;
    }

    private function toXmlScore(string $result): string
    {
        return match ($result) {
            Result::WIN      => '1',
            Result::DRAW()   => '0.5',
            Result::LOSS     => '0',
            Result::BYE_WIN  => '+',
            Result::BYE_LOSS => '-',
            default => throw new \UnexpectedValueException("Unknown game result: '$result'"),
        };
    }

    /** Returns [vkz, memberNumber] or ['', ''] if the ZPS string cannot be parsed. */
    private function parseZps(string $zps): array
    {
        $parts = explode('-', $zps, 2);
        if (
            count($parts) === 2
            && preg_match('/^[A-Za-z0-9]{5}$/', $parts[0])
            && $parts[1] !== ''
        ) {
            return [$parts[0], $parts[1]];
        }
        return ['', ''];
    }

    private function el(DOMDocument $dom, DOMElement $parent, string $tag, string $value): void
    {
        $el = $dom->createElement($tag);
        $el->appendChild($dom->createTextNode($value));
        $parent->appendChild($el);
    }

    /** Generates a random UUID v4. */
    private function randomUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40); // version 4
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80); // variant 10xx
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * Generates a deterministic UUID (v5-style, SHA-1 based) from the given input string.
     * Stable across re-exports of the same tournament.
     */
    private function stableUuid(string $input): string
    {
        $hash = sha1($input);
        $hash[12] = '5'; // version 5
        $hash[16] = dechex(hexdec($hash[16]) & 0x3 | 0x8); // variant 10xx
        return sprintf('%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }
}
