<?php

declare(strict_types=1);

namespace KaamelottGifboard\Service;

use KaamelottGifboard\DataObject\Character;
use KaamelottGifboard\DataObject\Gif;
use KaamelottGifboard\DataObject\GifIterator;
use Symfony\Component\String\UnicodeString;

class GifFinder
{
    public function __construct(private GifLister $lister)
    {
    }

    public function countGifs(): int
    {
        return $this->lister->gifs->count();
    }

    public function findGifs(): GifIterator
    {
        return $this->lister->gifs;
    }

    public function findGifsByQuote(string $search): array
    {
        $results = [];

        foreach ($this->lister->gifs as $gif) {
            if ($this->match($search, $gif->quote, true)) {
                $results[] = $gif;
            }
        }

        return $results;
    }

    public function findGifsByCharacter(string $search): ?array
    {
        $results = [];

        foreach ($this->lister->gifs as $gif) {
            foreach ($gif->charactersSpeaking as $character) {
                if ($this->match($search, $character->name)) {
                    $results[] = $gif;
                }
            }
        }

        return $results;
    }

    public function findGifsBySlug(string $slug): ?array
    {
        foreach ($this->lister->gifs as $key => $gif) {
            if ($gif->slug === $slug) {
                return $this->getGifByKey($key);
            }
        }

        return null;
    }

    public function findGifsByCode(string $code): ?Gif
    {
        foreach ($this->lister->gifs as $gif) {
            if ($gif->code === $code) {
                return $gif;
            }
        }

        return null;
    }

    public function findCharacter(string $search): ?Character
    {
        foreach ($this->lister->gifs as $gif) {
            foreach ($gif->characters as $character) {
                if ($this->match($search, $character->name)) {
                    return $character;
                }
            }
        }

        return null;
    }

    public function findCharacters(): array
    {
        $results = [];

        foreach ($this->lister->gifs as $gif) {
            foreach ($gif->characters as $character) {
                if (!\array_key_exists($character->slug, $results)) {
                    $results[$character->slug] = $character;
                }
            }
        }

        sort($results);

        return $results;
    }

    public function findRandom(): array
    {
        return $this->getGifByKey($this->lister->gifs->random());
    }

    private function match(string $search, string $subject, bool $clean = false): bool
    {
        if ($clean) {
            $search = (new UnicodeString($search))->ascii()->toString();
            $subject = (new UnicodeString($subject))->ascii()->toString();
        }

        return (bool) preg_match(sprintf('#%s#ui', $search), $subject);
    }

    private function getGifByKey(int $key): array
    {
        return [
            'previous' => $this->lister->gifs->prevElement($key),
            'current' => $this->lister->gifs->getElement($key),
            'next' => $this->lister->gifs->nextElement($key),
        ];
    }
}
