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

namespace Nelexa\GPlay\Scraper;

use GuzzleHttp\Psr7\Query;
use Nelexa\GPlay\GPlayApps;
use Nelexa\GPlay\HttpClient\ParseHandlerInterface;
use Nelexa\GPlay\Scraper\Extractor\AppsExtractor;
use Nelexa\GPlay\Util\ScraperUtil;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class ClusterAppsScraper implements ParseHandlerInterface
{
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array             $options
     *
     * @return array
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array &$options = []): array
    {
        $scriptData = ScraperUtil::extractScriptData($response->getBody()->getContents());
        $scriptDataInfo = ScraperUtil::getValue($scriptData, 'ds:3.0.1.0')
            ?? ScraperUtil::getValue($scriptData, 'ds:4.0.1.0');

        if ($scriptDataInfo === null) {
            return [[], null];
        }

        $scriptDataInfo = $scriptDataInfo[22] ?? $scriptDataInfo[21];

        $query = Query::parse($request->getUri()->getQuery());
        $locale = $query[GPlayApps::REQ_PARAM_LOCALE] ?? GPlayApps::DEFAULT_LOCALE;
        $country = $query[GPlayApps::REQ_PARAM_COUNTRY] ?? GPlayApps::DEFAULT_COUNTRY;

        $apps = [];

        foreach ($scriptDataInfo[0] as $data) {
            $apps[] = AppsExtractor::extractApp(isset($data[1]) ? $data : $data[0], $locale, $country);
        }

        $nextToken = $scriptDataInfo[1][3][1] ?? null;

        return [$apps, $nextToken];
    }
}
