<?php

class HasRolesAndAbilitiesTraitTest extends BaseTestCase
{
    public function test_list_abilities_gets_all_abilities()
    {
        $bouncer = $this->bouncer($user = User::create());

        $bouncer->allow('admin')->to('edit-site');
        $bouncer->allow($user)->to('create-posts');
        $bouncer->allow('editor')->to('edit-posts');
        $bouncer->assign('admin')->to($user);

        $this->assertEquals(
            ['create-posts', 'edit-site'],
            $user->getAbilities()->pluck('name')->sort()->values()->all()
        );
    }

    public function test_can_give_and_remove_abilities()
    {
        $gate = $this->gate($user = User::create());

        $user->allow('edit-site');

        $this->assertTrue($gate->allows('edit-site'));

        $user->disallow('edit-site');
        $this->clipboard->refresh();

        $this->assertTrue($gate->denies('edit-site'));
    }

    public function test_can_assign_and_retract_roles()
    {
        $bouncer = $this->bouncer($user = User::create());

        $bouncer->allow('admin')->to('edit-site');
        $user->assign('admin');

        $this->assertTrue($bouncer->allows('edit-site'));

        $user->retract('admin');
        $this->clipboard->refresh();

        $this->assertTrue($bouncer->denies('edit-site'));
    }

    public function test_can_check_roles()
    {
        $gate = $this->gate($user = User::create());

        $this->assertTrue($user->isNotA('moderator'));
        $this->assertTrue($user->isNotAn('editor'));

        $user->assign('moderator');
        $user->assign('editor');

        $this->assertTrue($user->is('moderator'));
        $this->assertTrue($user->is('editor'));
        $this->assertFalse($user->isNotAn('editor'));
        $this->assertFalse($user->is('admin'));
    }

    public function test_can_check_multiple_roles()
    {
        $gate = $this->gate($user = User::create());

        $this->assertTrue($user->isNotA('moderator', 'admin'));
        $this->assertTrue($user->isNotAn('editor', 'moderator'));
        $this->assertTrue($user->isNotAn('editor', 'moderator'));

        $user->assign('moderator');
        $user->assign('editor');

        $this->assertTrue($user->is('moderator', 'admin'));
        $this->assertTrue($user->is('editor', 'moderator'));
        $this->assertFalse($user->isNotAn('editor', 'moderator'));
        $this->assertTrue($user->isAll('editor', 'moderator'));
        $this->assertFalse($user->isAll('admin', 'moderator'));
        $this->assertFalse($user->isNotAn('editor', 'moderator'));
    }
}
