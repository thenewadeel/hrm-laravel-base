<?php

test('tests always capture mail with the array driver', function () {
    expect(config('mail.default'))->toBe('array');
});

test('the resend mailer transport is registered', function () {
    expect(config('mail.mailers.resend.transport'))->toBe('resend');
});

test('the resend mailer reads its api key from the services config', function () {
    expect(config('services.resend.key'))->toBe(env('RESEND_API_KEY'));
});
