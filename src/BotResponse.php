<?php

namespace App;

/**
 * Repostory of Bot response strings for use in SprintF
 */
class BotResponse {

    // Basic user interaction strings.
    public const OKAY = "Okay!";
    public const OKAY_USER = "Okay, %s!";
    public const IN_PROGRESS = "My Programmer is still working on that, <@%s>. Hold tight.";

    // PlusPlus
    public const PLUSPLUS = '%s is now at %s';
    public const PLUSPLUS_SINGLE = '%s is now at %s point';
    public const PLUSPLUS_FOR = '%s is now at %s, %s of which %s for %s';
    public const PLUSPLUS_YOURSELF = 'It\'s bad form to try to increase your own points, <@%s>... Be glad I don\'t take a point away.  You are still at %s points';
    public const PLUSPLUS_BOT = 'For me??? WooHoo!! I\'m at %s';
    public const PLUSPLUS_NOT_FOUND = 'I\'m sorry, I don\'t have a record of a previous ++ in this conversation. My cache expires after 72 hours.';

    public static function getFor(string $text) {
        return explode(' for ', $text, 2)[1] ?? '';
    }
}
