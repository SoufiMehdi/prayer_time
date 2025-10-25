<?php
// src/Command/TestHijriSchedulerCommand.php
namespace App\Command;

use App\Message\UpdateHijriHolidaysMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:test-hijri-scheduler',
    description: 'Test le scheduler Hijri manuellement'
)]
class TestHijriSchedulerCommand extends Command
{
    public function __construct(
        private MessageBusInterface $messageBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🚀 Envoi du message UpdateHijriHolidaysMessage...');
        $date = new \DateTime('2026-03-01');
        $this->messageBus->dispatch(new UpdateHijriHolidaysMessage($date));

        $output->writeln('✅ Message envoyé ! Lance maintenant : messenger:consume');

        return Command::SUCCESS;
    }
}
