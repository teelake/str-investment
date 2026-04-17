<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PermissionsTest extends TestCase
{
    public function testExpandGrantsWildcardYieldsFullCatalog(): void
    {
        $expanded = str_console_expand_grants(['*']);
        $catalog = str_console_permission_catalog();
        $this->assertSame(count($catalog), count($expanded));
        foreach (array_keys($catalog) as $key) {
            $this->assertContains($key, $expanded);
        }
    }

    public function testAuthorizeWithWildcardGrants(): void
    {
        $this->assertTrue(str_console_authorize(['*'], ['auth.session', 'loans.view']));
    }

    public function testAuthorizeDeniesMissingKey(): void
    {
        $this->assertFalse(str_console_authorize(['auth.session'], ['auth.session', 'loans.view']));
    }

    public function testValidatePermissionKeysRejectsUnknown(): void
    {
        $this->assertFalse(str_console_validate_permission_keys(['auth.session', 'not.a.real.key']));
        $this->assertTrue(str_console_validate_permission_keys(['auth.session', 'loans.view']));
    }

    public function testAuthorizeRouteUsesRouteMap(): void
    {
        $this->assertTrue(str_console_authorize_route(['*'], 'dashboard.index'));
        $this->assertFalse(str_console_authorize_route(['auth.session'], 'dashboard.index'));
    }
}
