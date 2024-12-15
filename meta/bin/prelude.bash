# This file should be sourced, not run
[[ -n $TRACE ]] && [[ $TRACE != 0 ]] && set -x

set -o errexit

cd $(dirname $0)/../..
base=$(pwd)

function warn {
    echo "$@" >&2
}

function die() {
    warn "$@"
    exit 1
}

function RUN() {
  [[ -n $DRY_RUN ]] && [[ $DRY_RUN != 0 ]] && _run=echo
  $_run "$@"
}

