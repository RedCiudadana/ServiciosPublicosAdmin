<?php
namespace Deployer;

require 'recipe/symfony.php';

// User chmod whie we learn about acl.
set('writable_mode', 'acl'); // chmod, chown, chgrp or acl.

// Project name
set('application', 'servicios.publicos');

// Project repository
set('repository', 'git@github.com:RedCiudadana/ServiciosPublicosAdmin.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', ['public/images']);

// Writable dirs by web server 
add('writable_dirs', ['public/images', 'node_modules']);


// Hosts

host('servicios-ocean')
    ->setHostname('142.93.77.143')
    ->setRemoteUser('redciudadana')
    ->set('remote_user', 'redciudadana')
    ->set('deploy_path', '/srv/web-apps/admin.tramites.redciudadana.org');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');


// Javascript deployment
after('deploy:vendors', 'deploy:vendors_js');

task('deploy:vendors_js', function () {
    run('cd {{release_path}} && npm install');
});

after('deploy:vendors_js', 'deploy:node');

task('deploy:node', function () {
    run('cd {{release_path}} && npm run build');
});

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

task('database:migrate', function() {
    $options = '--force --dump-sql';
    run(sprintf('{{bin/php}} {{bin/console}} doctrine:schema:update %s {{console_options}}', $options));
});
