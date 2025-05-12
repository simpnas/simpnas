#!/bin/bash

# Flags for requested fields
USERNAME_FLAG=false
GROUPS_FLAG=false
HOME_FLAG=false
SHELL_FLAG=false
SPACE_FLAG=false
COMMENT_FLAG=false
ENABLED_FLAG=false

# Enable all if no args
if [ "$#" -eq 0 ]; then
    USERNAME_FLAG=true
    GROUPS_FLAG=true
    HOME_FLAG=true
    SHELL_FLAG=true
    SPACE_FLAG=true
    COMMENT_FLAG=true
    ENABLED_FLAG=true
fi

# Parse CLI args
for arg in "$@"; do
    case "$arg" in
        username) USERNAME_FLAG=true ;;
        groups) GROUPS_FLAG=true ;;
        home_directory) HOME_FLAG=true ;;
        shell) SHELL_FLAG=true ;;
        space_used) SPACE_FLAG=true ;;
        comment) COMMENT_FLAG=true ;;
        user_enabled) ENABLED_FLAG=true ;;
    esac
done

# Process users from /etc/passwd
while IFS=: read -r USERNAME _ USER_ID GROUP_ID COMMENT HOME SHELL; do
    # Skip system users and nobody
    if [ "$USER_ID" -lt 1000 ] || [ "$USERNAME" = "nobody" ]; then
        continue
    fi

    # Initialize output variables
    GROUPS=""
    SPACE=""
    ENABLED="yes"

    echo "DEBUG id -Gn $USERNAME: $(id -Gn "$USERNAME" 2>&1)" >&2

    # Get groups using id -Gn (space-separated)
    if $GROUPS_FLAG; then
        GROUP_LIST=$(id -Gn "$USERNAME" 2>/dev/null | sed 's/ /, /g')
    fi

    # Get home usage
    if $SPACE_FLAG && [ -d "$HOME" ]; then
        SPACE=$(du -sh "$HOME" 2>/dev/null | cut -f1)
    fi

    # Get enabled status from passwd -S
    if $ENABLED_FLAG; then
        STATUS=$(passwd -S "$USERNAME" 2>/dev/null | awk '{print $2}')
        if [[ "$STATUS" == "L" || "$STATUS" == "LK" ]]; then
            ENABLED="no"
        fi
    fi

    # Construct fixed-order output
    OUT=()
    OUT+=("$USERNAME")          # username
    OUT+=("${GROUP_LIST}")      # groups (was GROUPS — renamed!)
    OUT+=("${HOME}")
    OUT+=("${SHELL}")
    OUT+=("${SPACE:-}")
    OUT+=("${COMMENT}")
    OUT+=("${ENABLED}")

    IFS='|'; echo "${OUT[*]}"; unset IFS
done < /etc/passwd
