<?php
namespace Deployer;

require 'recipe/magento2.php';

set('keep_releases', 10);
set('cleanup_use_sudo', false);



// Project repository
set('repository', 'https://github.com/vijdev389/impactbatteryfinal.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// [Optional] Allocate timeout value for commands. Default value is 300.
set('default_timeout', 1200);

set('http_user', 'ae05e2da_8');

add('shared_files', [
    'auth.json',
    'app/etc/env.php',
    'var/.maintenance.ip',
    'var/.maintenance.flag',
    'pub/robots.txt'
]);
add('shared_dirs', [
    'var/composer_home',
    'var/log',
    'var/export',
    'var/report',
    'var/import',
    'var/import_history',
    'var/session',
    'var/importexport',
    'var/backups',
    'var/tmp',
    // 'pub/sitemap',
    'pub/media',
    'pub/sitemaps'
]);
add('writable_dirs', [
    'var',
    'pub/static',
    'pub/media',
    'generated'
]);

add('clear_paths', [
    'generated/*',
    'pub/static/_cache/*',
    'var/generation/*',
    'var/cache/*',
    'var/page_cache/*',
    'var/view_preprocessed/*'
]);

// Hosts Nexcess Dev Server 
host('fef55b1fbd.nxcli.net')
    ->set('remote_user', 'ae05e2da_8')
    ->set('deploy_path', '/chroot/home/ae05e2da/deploy')
    ->set('labels', ['stage' => 'nexcessdev']);

// Hosts Nexcess Live Server 


// Hooks

after('deploy:failed', 'deploy:unlock');
