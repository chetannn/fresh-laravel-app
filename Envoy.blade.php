@servers(['web' => ['root@64.227.130.20'] ])

@setup
$now = time();
$releaseDirectory = '/var/www/html/releases';
$storageDirectory = '/var/www/html/storage';
$envFilePath = '/var/www/html';
$currentReleaseDirectory = '/var/www/html/current';
$repositoryUrl = 'https://github.com/chetannn/fresh-laravel-app';
@endsetup

@story('deploy')
        clone-new-release
        install-dependencies
        link-storage-directory
        link-env-file
        change-storage-directory-permission
        active-new-release
        purge-old-releases
@endstory

@task('clone-new-release', ['on' => 'web'])
        mkdir -p {{ $releaseDirectory }}
        cd {{ $releaseDirectory }}
        git clone {{ $repositoryUrl }} {{ $now }}
@endtask

@task('install-dependencies', ['on' => 'web'])
        cd {{ $releaseDirectory}}/{{ $now }}
        composer install --optimize-autoloader --no-dev
        rm -rf ./storage
@endtask


@task('link-storage-directory', ['on' => 'web'])
                mkdir -p {{ $storageDirectory }}/{app,framework,logs}
                mkdir -p  {{ $storageDirectory }}/app/public
                mkdir -p {{ $storageDirectory }}/framework/{cache,sessions,testing,views}
                ln -s -f {{ $storageDirectory }} {{ $releaseDirectory}}/{{ $now }}
@endtask


@task('link-env-file', ['on' => 'web'])
        cd {{ $envFilePath }} && touch .env
        ln -s -f {{ $envFilePath }}/.env {{ $releaseDirectory}}/{{ $now }}
@endtask

@task('change-storage-directory-permission', ['on' => 'web'])
        chown -R www-data:www-data {{ $storageDirectory }}
@endtask

@task('active-new-release', ['on' => 'web'])
        ln -s -n -f {{ $releaseDirectory }}/{{ $now }} {{ $currentReleaseDirectory }}
        service php8.1-fpm reload
@endtask

@task('purge-old-releases', ['on' => 'web'])
        cd  {{ $releaseDirectory }} && ls -t -1 | tail -n +6 | xargs rm -rf
@endtask

