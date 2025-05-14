#!/bin/bash

# Define flags for each field (default: false)
declare -A FLAGS=(
    [username]=false
    [groups]=false
    [home_directory]=false
    [shell]=false
    [space_used]=false
    [comment]=false
    [user_enabled]=false
)

# Enable all flags if no arguments were provided
if [ "$#" -eq 0 ]; then
    for key in "${!FLAGS[@]}"; do FLAGS[$key]=true; done
else
    for arg in "$@"; do
        [[ -v FLAGS[$arg] ]] && FLAGS[$arg]=true
    done
fi

# Read users from /etc/passwd
while IFS=: read -r USERNAME _ USER_ID GROUP_ID COMMENT HOME SHELL; do
    [ "$USER_ID" -lt 1000 ] || [ "$USERNAME" = "nobody" ] && continue


    # Initialize all fields to empty
    GROUP_LIST=""
    SPACE=""
    ENABLED="yes"

    # Groups (comma-separated)
    if ${FLAGS[groups]}; then
        GROUP_LIST=$(id -Gn "$USERNAME" 2>/dev/null | tr ' ' ',' | xargs)
    fi

    # Space used in home directory
    if ${FLAGS[space_used]} && [ -d "$HOME" ]; then
        SPACE=$(du -sh "$HOME" 2>/dev/null | cut -f1)
    fi

    # Enabled status
    if ${FLAGS[user_enabled]}; then
        STATUS=$(passwd -S "$USERNAME" 2>/dev/null | awk '{print $2}')
        [[ "$STATUS" == "L" || "$STATUS" == "LK" ]] && ENABLED="no"
    fi

    # Output all 7 fields in fixed order (regardless of which were requested)
    echo "$USERNAME|$GROUP_LIST|$HOME|$SHELL|$SPACE|$COMMENT|$ENABLED"

done < /etc/passwd
