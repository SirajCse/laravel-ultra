<?php

namespace LaravelUltra\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

        // Publish migrations
        $this->call('vendor:publish', ['--tag' => 'ultra-migrations']);

        // Run migrations
        $this->call('migrate');

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
        $this->info('💡 Run "php artisan ultra:demo" to see examples.');
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

        $this->call('vendor:publish', ['--tag' => 'ultra-views']);
        $this->call('vendor:publish', ['--tag' => 'ultra-assets']);

        $this->info('✅ Demo components installed');
        $this->info('🌐 Visit /ultra-demo to see examples');
    }
}