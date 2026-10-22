<?php

use Illuminate\Support\Arr;

it('has every English key in each locale', function (string $locale) {
    $lang = dirname(__DIR__, 2).'/resources/lang';

    expect(array_keys(Arr::dot(require "{$lang}/{$locale}/autentique.php")))
        ->toEqualCanonicalizing(array_keys(Arr::dot(require "{$lang}/en/autentique.php")));
})->with(fn (): array => array_values(array_diff(scandir(dirname(__DIR__, 2).'/resources/lang'), ['.', '..', 'en'])));
