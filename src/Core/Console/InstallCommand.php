<?php

namespace LaravelUltra\Core\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'ultra:install 
                            {--ai : Enable AI features}
                            {--realtime : Enable real-time features}
                            {--with-demo : Install demo components}';

    protected $description = 'Install Laravel Ultra';

    public function handle()
    {
        $this->info('🚀 Installing Laravel Ultra...');

        // Publish config
        $this->call('vendor:publish', ['--tag' => 'ultra-config']);

        if ($this->option('ai')) {
            $this->installAIFeatures();
        }

        if ($this->option('realtime')) {
            $this->installRealtimeFeatures();
        }

        if ($this->option('with-demo')) {
            $this->installDemo();
        }

        $this->info('🎉 Laravel Ultra installed successfully!');
        $this->info('💡 Usage: Ultra::table(User::class)->toResponse(request())');
    }

    protected function installAIFeatures()
    {
        $this->info('🤖 Configuring AI features...');

        if (!env('OPENAI_API_KEY')) {
            $this->warn('⚠️  OPENAI_API_KEY not set in .env file');
            $this->info('💡 Add: OPENAI_API_KEY=your_openai_api_key_here');
        }

        $this->info('✅ AI features configured');
    }

    protected function installRealtimeFeatures()
    {
        $this->info('🔌 Configuring real-time features...');

        if (!env('BROADCAST_DRIVER')) {
            $this->warn('⚠️  BROADCAST_DRIVER not set in .env file');
            $this->info('💡 Add: BROADCAST_DRIVER=pusher');
        }

        $this->info('✅ Real-time features configured');
    }

    protected function installDemo()
    {
        $this->info('📦 Installing demo components...');

        $this->call('vendor:publish', ['--tag' => 'ultra-assets']);

        $this->info('✅ Demo components installed');
    }
}