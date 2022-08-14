<?php

declare(strict_types=1);

/*
 * Copyright (c) Ne-Lexa
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/Ne-Lexa/google-play-scraper
 */

namespace Nelexa\GPlay\Scraper\Extractor;

use Nelexa\GPlay\Model\AppId;
use Nelexa\GPlay\Model\GoogleImage;
use Nelexa\GPlay\Model\ReplyReview;
use Nelexa\GPlay\Model\Review;
use Nelexa\GPlay\Util\DateStringFormatter;

/**
 * @internal
 */
class ReviewsExtractor
{
    /**
     * @param AppId $requestApp
     * @param array $data
     *
     * @return array
     */
    public static function extractReviews(AppId $requestApp, array $data): array
    {
        $reviews = [];

        foreach ($data as $reviewData) {
            $reviews[] = self::extractReview($requestApp, $reviewData);
        }

        return $reviews;
    }

    /**
     * @param AppId $requestApp
     * @param       $reviewData
     *
     * @return Review
     */
    public static function extractReview(AppId $requestApp, $reviewData): Review
    {
        $reviewId = $reviewData[0];
        $userName = is_string($reviewData[1][0]) ? $reviewData[1][0] : '';
        $avatar = !empty($reviewData[1][1][3][2])
            ? (new GoogleImage($reviewData[1][1][3][2]))->setSize(64)
            : null;
        $date = !empty($reviewData[5][0])
            ? DateStringFormatter::unixTimeToDateTime($reviewData[5][0])
            : null;
        $score = $reviewData[2] ?? 0;
        $text = (string) ($reviewData[4] ?? '');
        $likeCount = $reviewData[6] ?? 0;
        $appVersion = $reviewData[10] ?? null;

        $reply = self::extractReplyReview($reviewData);

        return new Review(
            $reviewId,
            $userName,
            $text,
            $avatar,
            $date,
            $score,
            $likeCount,
            $reply,
            $appVersion
        );
    }

    /**
     * @param array $reviewData
     *
     * @return ReplyReview|null
     */
    private static function extractReplyReview(array $reviewData): ?ReplyReview
    {
        if (isset($reviewData[7][1])) {
            $replyText = $reviewData[7][1];
            $replyDate = DateStringFormatter::unixTimeToDateTime($reviewData[7][2][0]);

            if ($replyText && $reviewData) {
                return new ReplyReview(
                    $replyDate,
                    $replyText
                );
            }
        }

        return null;
    }
}
