<?php

namespace Tests\Unit;

use App\Support\PhoneNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PhoneNormalizerTest extends TestCase
{
    #[DataProvider('validPhonesProvider')]
    public function test_is_valid_accepts_indonesian_phone_numbers(string $phone): void
    {
        $this->assertTrue(PhoneNormalizer::isValid($phone));
    }

    /** @return list<array{0: string}> */
    public static function validPhonesProvider(): array
    {
        return [
            ['08123456789'],
            ['081234567890'],
            ['628123456789'],
            ['62812345678'],
            ['0812-3456-7890'],
            ['+628123456789'],
        ];
    }

    #[DataProvider('invalidPhonesProvider')]
    public function test_is_valid_rejects_invalid_phone_numbers(?string $phone): void
    {
        $this->assertFalse(PhoneNormalizer::isValid($phone));
    }

    /** @return list<array{0: ?string}> */
    public static function invalidPhonesProvider(): array
    {
        return [
            ['0812345678'],
            ['0812345678901'],
            ['71234567890'],
            ['abc'],
            ['12345'],
        ];
    }

    public function test_is_valid_allows_empty_for_nullable_fields(): void
    {
        $this->assertTrue(PhoneNormalizer::isValid(null));
        $this->assertTrue(PhoneNormalizer::isValid(''));
        $this->assertTrue(PhoneNormalizer::isValid('   '));
    }

    public function test_validation_rules_reject_too_short_phone(): void
    {
        $validator = validator(
            ['phone' => '0812345678'],
            ['phone' => PhoneNormalizer::validationRules(true)]
        );

        $this->assertTrue($validator->fails());
        $this->assertSame(
            PhoneNormalizer::validationMessage(),
            $validator->errors()->first('phone')
        );
    }

    public function test_validation_rules_accept_valid_phone(): void
    {
        $validator = validator(
            ['phone' => '081234567890'],
            ['phone' => PhoneNormalizer::validationRules(true)]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rules_allow_empty_when_not_required(): void
    {
        $validator = validator(
            ['phone' => ''],
            ['phone' => PhoneNormalizer::validationRules()]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rules_allow_unchanged_legacy_phone(): void
    {
        $validator = validator(
            ['phone' => '0812345678'],
            ['phone' => PhoneNormalizer::validationRules(unchangedFrom: '0812345678')]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rules_reject_changed_invalid_phone(): void
    {
        $validator = validator(
            ['phone' => '0812345678'],
            ['phone' => PhoneNormalizer::validationRules(unchangedFrom: '081234567890')]
        );

        $this->assertTrue($validator->fails());
    }
}
