function help_command() {
  cat <<END;
####### Overridden or additional commands #######
 
END
}

function run_in_container() {    
    if [[ -e /proc/self/cgroup ]] && grep -q "docker" /proc/self/cgroup && [[ "$USER" != "gitpod" ]] ; then
        bash -c "$1"
    else
        docker-compose exec php bash -c "$1"
    fi
    return $?
}

function err() { echo -e "\033[0;31mERROR: $@\033[0m" >&2;  }
function wrn() { echo -e "\033[0;33mWARNING: $@\033[0m" >&2;  }
function inf() { echo -e "\033[0;44mINFO: $@\033[0m" >&2;  }

function main() {
    command="$1" && shift
    
    command_found=0
    
    case "$command" in
        help_override)     help_command ;;
 
    esac
    
    exit $command_found;
}

main "$@"
