#!bash
#
# see https://github.com/KnpLabs/KnpConsoleAutocompleteBundle/blob/master/Resources/Shells/symfony2-completion.bash
#
# bash completion support for symfony2 console
#
# Copyright (C) 2010 Matthieu Bontemps <matthieu@knplabs.com>
# Distributed under the GNU General Public License, version 2.0.

_auto()
{
    local cur prev opts cmd
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    cmd="${COMP_WORDS[0]}"

    # Launch the autocomplete command.
    opts=$(${cmd} autocomplete)
    if [[ ${COMP_CWORD} = 1 ]] ; then
        COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
        return 0
    fi
    if [[ ${COMP_CWORD} = 2 ]] ; then
        case "${prev}" in
            help)
                COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
                return 0
                ;;
            *)
                ;;
        esac
    fi
    if [[ ${COMP_CWORD} > 1 ]] ; then
        case "${cur}" in
            -*)
                COMPREPLY=( $(compgen -W "$(${cmd} autocomplete ${COMP_WORDS[1]})" -- ${cur}) )
                return 0
                ;;
            *)
                return 1
                ;;
        esac
    fi

    COMPREPLY=( $(compgen -f ${cur}) )
    return 0
}
complete -o default -F _auto auto
COMP_WORDBREAKS=${COMP_WORDBREAKS//:}