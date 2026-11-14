<?php declare(strict_types=1);

/**
 * Test: App\Presentation\Backend\Accessory\Dashboard\DashboardWidgets
 */

use App\Presentation\Backend\Accessory\Dashboard\DashboardWidgets;
use Nette\Security\IIdentity;
use Nette\Security\Permission;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Nette\Security\UserStorage;
use Tester\Assert;
use Tracy\Debugger;

require __DIR__ . '/../../../../bootstrap.php';


final class SignedInStorage implements UserStorage
{
	public function __construct(
		private IIdentity $identity,
	) {
	}


	public function saveAuthentication(IIdentity $identity): void
	{
	}


	public function clearAuthentication(bool $clearIdentity): void
	{
	}


	public function getState(): array
	{
		return [true, $this->identity, null];
	}


	public function setExpiration(?string $expire, bool $clearIdentity): void
	{
	}
}


/** A signed-in editor who may open the blog only. */
function editor(): User
{
	$acl = new Permission;
	$acl->addRole('editor');
	foreach (['Backend:Blog', 'Backend:Permission'] as $resource) {
		$acl->addResource($resource);
	}

	$acl->allow('editor', 'Backend:Blog', 'default');
	return new User(storage: new SignedInStorage(new SimpleIdentity(1, ['editor'])), authorizator: $acl);
}


/** @return array{type: string, link: string, title: string, order?: int} */
function widget(string $type, string $link, string $title, ?int $order = null): array
{
	return ['type' => $type, 'link' => $link, 'title' => $title] + ($order !== null ? ['order' => $order] : []);
}


test('Widgets of a page the user may not open are left out', function () {
	$widgets = new DashboardWidgets([
		fn(): array => [widget('tile', 'Blog:', 'Comments')],
		fn(): array => [widget('tile', 'Permission:users', 'Users')],
	]);

	$built = $widgets->build(editor());
	Assert::same(['Comments'], array_column($built['tiles'], 'title'));
	Assert::same([], $built['blocks']);
});


test('Tiles and blocks are split and sorted by order', function () {
	$widgets = new DashboardWidgets([
		fn(): array => [
			widget('tile', 'Blog:', 'Second', 20),
			widget('chart', 'Blog:', 'Chart'),
			widget('ranking', 'Blog:', 'Ranking', 50),
		],
		fn(): array => [widget('tile', 'Blog:', 'First', 10)],
	]);

	$built = $widgets->build(editor());
	Assert::same(['First', 'Second'], array_column($built['tiles'], 'title'));
	Assert::same(['Ranking', 'Chart'], array_column($built['blocks'], 'title'));
});


test('A widget service that throws is left out', function () {
	Debugger::$logDirectory = sys_get_temp_dir();
	$widgets = new DashboardWidgets([
		fn(): array => throw new RuntimeException('Table blog_comment does not exist.'),
		fn(): array => [widget('tile', 'Blog:', 'Comments')],
	]);

	$built = $widgets->build(editor());
	Assert::same(['Comments'], array_column($built['tiles'], 'title'));
});
