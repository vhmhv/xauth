<?php

namespace vhmhv\Xauth\Commands;

use Illuminate\Console\Command;

class XauthCommand extends Command
{
    public $signature = 'xauth';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
