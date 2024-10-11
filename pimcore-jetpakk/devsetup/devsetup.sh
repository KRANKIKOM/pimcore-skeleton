#!/usr/bin/env bash

function help_command() {
  cat <<END;
USAGE:
  ./devsetup.sh <command>

COMMANDS:
  help                    Display detailed help

  up                      start web
  up-debug                start web and launch php container with xdebug
  down                    stops web
  shell                   starts shell on php server
  migrate                 runs pimcore migrations
  migrate-core            runs pimcore core migrations
  classes-rebuild         runs pimcore class rebuild
  composer-install        runs composer install and migrate-core
  pimcore-update          use this after someone told you to
  cache-clear             clears all caches we know
  phpcs                   runs static analysis via phpcs
  twigcs                  runs static analysis via twigcs
  psalm                   runs static analysis via psalm
  test-all                runs psalm, phpcs and twigcs
  frontend-logs           shows frontend build logs
  searchinit              Intialize search indexes
  searchindex             (Re-)index content
  searchfull              Initialize search indexes and re-index content
  restore-prod-backup     Restore last nights production backup

  you can also always do it directly
  >>> source .envrc && docker-compose <your fancy command> <<<

END
  
  # Override mechanismn
  if [ -f "devsetup.override.sh" ]; then
      ./devsetup.override.sh help_override
  fi

  exit 1
}

function source_env() {
    source .envrc
}

function create_proxy_network() {  
    if [ "$PIMCORE_DEVSETUP_NATIVEDOCKER" == "1" ]; then  
        docker network list|grep proxy || docker network create proxy 2>&1 >/dev/null
    fi
}

function create_vscode_launch_config() {
    mkdir -p .vscode
    if [ ! -f ".vscode/launch.json" ]; then
        mkdir -p .vscode
        cat <<'EOF' > .vscode/launch.json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for XDebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
EOF
    fi
}

function up_command() {
    debug=$1
    compose_files="-f docker-compose.yml"
    compose_extra_params=""

    # Check if docker-compose.override.yml exists
    if [ -f "docker-compose.override.yml" ]; then
        compose_files="$compose_files -f docker-compose.override.yml"
    fi

    if [ "$debug" == "debug" ]; then
        create_vscode_launch_config
        compose_files="$compose_files -f vendor/krankikom/pimcore-jetpakk/containers/docker-compose.debug.override.yml"
        compose_extra_params="$compose_extra_params --build"
    fi

    create_proxy_network
    
    ./docker-build.sh -o
    
    if [ "$PIMCORE_DEVSETUP_NATIVEDOCKER" == "1" ]; then  
        if [ "$debug" == "debug" ]; then
            compose_files="$compose_files -f vendor/krankikom/pimcore-jetpakk/containers/docker-compose.debug.override.yml"
        fi
        
    else         
        host_to_wait_for=`echo $VAGRANT_DNS |awk -F',' '{printf "%s", $1}'`
        vagrant up && \
        echo -n "waiting for docker-daemon to start " && \
        while ! echo exit | nc $host_to_wait_for 2375 >> /dev/null; do sleep 2; echo -n "."; done        
    fi

    $DOCKER_COMPOSE_CMD $compose_files up -d $compose_extra_params
    
    if $DOCKER_COMPOSE_CMD exec php bin/console doctrine:migrations:list --no-ansi | grep "not migrated" > /dev/null; then
        wrn "There are new Migrations, you might want to run"
        wrn "./devsetup.sh migrate"
    fi
}


function down_command() {
    create_proxy_network

    $DOCKER_COMPOSE_CMD down
    if [ "$PIMCORE_DEVSETUP_NATIVEDOCKER" != "1" ]; then 
        vagrant halt
    fi
}

function shell_command() {
    create_proxy_network

    $DOCKER_COMPOSE_CMD exec php bash
}

function run_in_container() {    
    if [[ -e /proc/self/cgroup ]] && grep -q "docker" /proc/self/cgroup && [[ "$USER" != "gitpod" ]] ; then
        bash -c "$1"
    else
        $DOCKER_COMPOSE_CMD exec php bash -c "$1"
    fi
    return $?
}

function migrate_command() {
    run_in_container "bin/console doctrine:migrations:migrate --prefix=App\\\\Migrations"
}

function migrate_core_command() {
    run_in_container "bin/console doctrine:migrations:migrate --prefix=Pimcore\\\\Bundle\\\\CoreBundle -n"
}

function classes_rebuild_command() {
    run_in_container "bin/console pimcore:deployment:classes-rebuild -cd"
}

function composer_install_command() {
    inf "Running composer"
    run_in_container "COMPOSER_MEMORY_LIMIT=-1 composer install";
    inf "Running migrations"
    migrate_core_command
}

function psalm_command() {
    inf "Psalm $1"
    run_in_container " php -dopcache.enable_cli=1 -dopcache.jit=0 ./vendor/bin/psalm --memory-limit=20G --long-progress --no-cache $1"
}

function twigcs_command() {
    run_in_container "vendor/bin/twigcs $1"
}

function phpcs_command() {
    run_in_container "php vendor/bin/phpcs --standard=phpcs-ruleset.xml -p $1"
}

function test_all_command(){
    if ! (psalm_command && phpcs_command && twigcs_command); then
        wrn "Sorry, one check failed. Look above for details"
    else
        inf "\o/ \o/ \o/ \o/             ALL OK                \o/ \o/ \o/ \o/"
    fi
}

function cache_clear_command() {
    inf "Clearing pimcore cache"
    run_in_container "bin/console pimcore:cache:clear"
    inf "Clearing symfony cache"
    run_in_container "bin/console cache:clear"
    inf "Clearing psalm cache"
    run_in_container "vendor/bin/psalm --clear-cache"
}

function pimcore_update_command() {
    clear
    wrn "This will checkout master branch and try to update your pimcore."
    read -p "Did you commit all local changes? (y/n) " answer

    while true
    do
      case $answer in
       [yY]* ) do_pimcore_update;
               break;;

       [nN]* ) exit;;

       * )     echo "Dude, just enter Y or N, please."; break ;;
      esac
    done
}

function frontendlogs_command() {
    $DOCKER_COMPOSE_CMD logs -f --tail="300" frontend-build
}

function do_pimcore_update() {
    inf "updating...";
    git checkout master;
    git pull origin master;
    composer_install_command;
    classes_rebuild_command;
    cache_clear_command;
    searchinit_command;
    searchindex_command;
    wrn "!!! You're on master now !!!"
}

function searchinit_command() {
    inf "Creating index mappings"
    run_in_container "bin/console dynamic-search:es:rebuild-index-mapping -vv -c ort_site"
    run_in_container "bin/console dynamic-search:es:rebuild-index-mapping -vv -c ort_recipe"
    run_in_container "bin/console dynamic-search:es:rebuild-index-mapping -vv -c ort_nutrient"
}

function searchindex_command() {
    inf "Building search index"
    run_in_container "bin/console dynamic-search:run -c ort_site -f"
    run_in_container "bin/console dynamic-search:run -c ort_recipe -f"
    run_in_container "bin/console dynamic-search:run -c ort_nutrient -f"
}

function restore_prod_backup_command() {
    inf "Restoring prod backup"
    run_in_container "kk-tools/backup_restore_content_only.sh $1 $2 $3 $4 $5 $6 $7 $8 $9"
    run_in_container "bin/console pimcore:user:reset-password admin -p admin -n"
    run_in_container "bin/console app:post-backup-restore:set-site-domains"
    run_in_container "bin/console app:post-backup-restore:delete-sent-emails"
    run_in_container "bin/console app:post-backup-restore:delete-salesforce-forms-submissions"
    run_in_container "bin/console app:post-backup-restore:configure-non-prod-salesforce-forms"
    run_in_container "bin/console app:post-backup-restore:set-doccheck-ids"
    cache_clear_command
}


function hddcheck_command() {
    vhdd=$(docker run -it --rm --privileged --pid=host justincormack/nsenter1 /bin/df /dev/vda1 | grep /dev/vda1 )
    if [ -z "$vhdd" ]; then
        wrn "Could not determine amount of free space in docker virtual disk #1"
        return
    fi 
    hddavail=$(echo $vhdd|awk '{print $4}')
    if [ -z "$hddavail" ]; then
	    wrn "Could not determine amount of free space in docker virtual disk #2"
	    return
    fi 
    hddavail=$(($hddavail/1024/1024))
    if ! echo "$hddavail" | grep -qE '^[0-9]+$'; then
	    wrn "Could not determine amount of free space in docker virtual disk #3"
    elif (( $hddavail < 15 )); then
        wrn "Low space in docker virtual disk left $hddavail GB. Consider increasing Virtual Disk Limit. Docker -> Settings -> Resources";
    elif (( $hddavail < 10 )); then
        err "Low space in docker virtual disk left $hddavail GB. Increase Virtual Disk Limit in Docker -> Settings -> Resources";
        exit 255;
    fi
}

function pre_flight_check() {
    which direnv >/dev/null
    if [ $? == "1" ]; then
        echo "ERROR: 'direnv' is not installed. For Installation Guide see https://direnv.net/docs/installation.html"
        exit 3
    fi

    hddcheck_command

    if [ ! -d ~/.composer ]; then
        inf "Creating local composer cache/config dir"
        mkdir -p ~/.composer
    fi

}


function err() { echo -e "\033[0;31mERROR: $@\033[0m" >&2;  }
function wrn() { echo -e "\033[0;33mWARNING: $@\033[0m" >&2;  }
function inf() { echo -e "\033[0;44mINFO: $@\033[0m" >&2;  }

function main() {
    source_env
    command="$1" && shift

    # Override mechanism
    if [ -f "devsetup.override.sh" ]; then
        ./devsetup.override.sh "$command"
        if [ $? -eq 150 ]; then
            exit 0;
        fi    
    fi

    case "$command" in

    help)                 help_command ;;
    up)                   up_command ;;
    up-debug)             up_command "debug" ;;
    down)                 down_command ;;
    shell)                shell_command ;;
    migrate)              migrate_command ;;
    classes-rebuild)      classes_rebuild_command ;;
    composer-install)     composer_install_command ;;
	  migrate-core)         migrate_core_command ;;
    pimcore-update)       pimcore_update_command ;;
    cache-clear)          cache_clear_command ;;
    psalm)                psalm_command $1;;
    twigcs)               twigcs_command $1;;
    phpcs)                phpcs_command $1;;
    test-all)             test_all_command;;
    searchinit)           searchinit_command ;;
    searchindex)          searchindex_command ;;
    searchfull)           searchinit_command; searchindex_command ;;
    frontend-logs)        frontendlogs_command ;;
    restore-prod-backup)  restore_prod_backup_command $1 $2 $3 $4 $5 $6 ;;
    hddcheck)             hddcheck_command ;;

    # Unknown command
    *)  err "Unknown command '$command'"; help_command ;;
  esac
}

pre_flight_check
main "$@"
