<?php

require_once __DIR__ . '/../config/constants.php';

final class MentionJury
{
    public static function calculer(float $note): string
    {
        if ($note < NOTE_MINIMALE || $note > NOTE_MAXIMALE) {
            throw new InvalidArgumentException('La note doit être comprise entre 0 et 20.');
        }

        if ($note < 10) {
            return 'Ajourné';
        }
        if ($note < 12) {
            return 'Passable';
        }
        if ($note < 14) {
            return 'Assez bien';
        }
        if ($note < 16) {
            return 'Bien';
        }

        return 'Très bien';
    }
}
