<?php
declare(strict_types=1);

namespace adeynes\cucumber\command;

use adeynes\cucumber\Cucumber;
use adeynes\cucumber\mod\Mute;
use adeynes\cucumber\utils\Queries;
use adeynes\parsecmd\command\blueprint\CommandBlueprint;
use adeynes\parsecmd\command\ParsedCommand;
use pocketmine\command\CommandSender;

class MutelistCommand extends PunishmentListCommand
{

    public function __construct(Cucumber $plugin, CommandBlueprint $blueprint)
    {
        parent::__construct(
            $plugin,
            $blueprint,
            'mutelist',
            'cucumber.command.mutelist',
            'See the list of mutes',
            '/mutelist'
        );
    }

    public function getSelectQuery(): string
    {
        return Queries::CUCUMBER_GET_PUNISHMENTS_MUTES_LIMITED;
    }

    /**
     * @param array $mute_row The database representation of a mute
     * @return string
     */
    protected function makeMuteInfoLine(array $mute_row): string {
        return $this->getPlugin()->formatMessageFromConfig(
            'success.mutelist.list',
            Mute::from($mute_row)->getDataFormatted()
        );
    }

    protected function sendList(CommandSender $sender, array $mutes, int $page_number): void {
        $page = trim(
            $this->getPlugin()->formatMessageFromConfig(
                'success.mutelist.intro',
                ['page' => $page_number]
            ) . PHP_EOL .
            implode(PHP_EOL, array_map([$this, 'makeMuteInfoLine'], $mutes))
        );
        $sender->sendMessage($page);
    }

}