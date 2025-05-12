#!/bin/bash

VOLUMES_DIR="/volumes"

# Flags for requested fields
VOLUME_FLAG=false
DISK_FLAG=false
MODEL_FLAG=false
SERIAL_FLAG=false
HEALTH_FLAG=false
TEMP_FLAG=false
SIZE_FLAG=false
USE_PERCENT_FLAG=false
TOTAL_SPACE_FLAG=false
USED_SPACE_FLAG=false
FREE_SPACE_FLAG=false
PARTUUID_FLAG=false
FILESYSTEM_FLAG=false
IS_RAID_FLAG=false
IS_CRYPT_FLAG=false
IS_MOUNTED_FLAG=false
TYPE_FLAG=false

# Enable all flags if no arguments are passed
if [ "$#" -eq 0 ]; then
    VOLUME_FLAG=true
    DISK_FLAG=true
    MODEL_FLAG=true
    SERIAL_FLAG=true
    HEALTH_FLAG=true
    TEMP_FLAG=true
    SIZE_FLAG=true
    USE_PERCENT_FLAG=true
    TOTAL_SPACE_FLAG=true
    USED_SPACE_FLAG=true
    FREE_SPACE_FLAG=true
    PARTUUID_FLAG=true
    FILESYSTEM_FLAG=true
    IS_RAID_FLAG=true
    IS_CRYPT_FLAG=true
    IS_MOUNTED_FLAG=true
    TYPE_FLAG=true
fi

# Parse arguments
for arg in "$@"; do
    case "$arg" in
        volume) VOLUME_FLAG=true ;;
        disk) DISK_FLAG=true ;;
        model) MODEL_FLAG=true ;;
        serial) SERIAL_FLAG=true ;;
        health) HEALTH_FLAG=true ;;
        temp) TEMP_FLAG=true ;;
        size) SIZE_FLAG=true ;;
        use_percent) USE_PERCENT_FLAG=true ;;
        total_space) TOTAL_SPACE_FLAG=true ;;
        used_space) USED_SPACE_FLAG=true ;;
        free_space) FREE_SPACE_FLAG=true ;;
        partuuid) PARTUUID_FLAG=true ;;
        filesystem) FILESYSTEM_FLAG=true ;;
        is_raid) IS_RAID_FLAG=true ;;
        is_crypt) IS_CRYPT_FLAG=true ;;
        is_mounted) IS_MOUNTED_FLAG=true ;;
        type) TYPE_FLAG=true ;;
    esac
done

# Loop through volumes
for volume in "$VOLUMES_DIR"/*; do
    if [ -d "$volume" ]; then
        NAME=$(basename "$volume")
        IS_MOUNTED="no"
        if mountpoint -q "$volume"; then
            IS_MOUNTED="yes"
        fi

        DISK=$(df "$volume" 2>/dev/null | awk 'NR==2 {print $1}')
        BASE_DISK=$(basename "$DISK" | sed 's/[0-9]*$//' | sed 's/p[0-9]*$//')

        # Gather values based on flags
        if $MODEL_FLAG || $SERIAL_FLAG; then
            SMART_INFO=$(smartctl -i "$DISK" 2>/dev/null)
        fi

        MODEL=$($MODEL_FLAG && echo "$SMART_INFO" | grep -i "Device Model" | awk -F': ' '{print $2}' || echo "")
        SERIAL=$($SERIAL_FLAG && echo "$SMART_INFO" | grep -i "Serial Number" | awk -F': ' '{print $2}' || echo "")

        if $HEALTH_FLAG; then
            HEALTH=$(smartctl -H "$DISK" 2>/dev/null | grep -i "SMART overall-health" | awk -F': ' '{print $2}' | xargs)
        fi

        if $TEMP_FLAG; then
            TEMP=$(smartctl -A "$DISK" 2>/dev/null | grep -i "Temperature_Celsius" | awk '{print $10}' | xargs)
        fi

        if $SIZE_FLAG; then
            DU_SIZE=$(du -sh "$volume" 2>/dev/null | cut -f1)
        fi

        if $USE_PERCENT_FLAG || $TOTAL_SPACE_FLAG || $USED_SPACE_FLAG || $FREE_SPACE_FLAG; then
            read -r TOTAL USED AVAIL USEP <<< $(df -h --output=size,used,avail,pcent "$volume" 2>/dev/null | tail -n 1)
        fi

        if $PARTUUID_FLAG; then
            PARTUUID=$(blkid "$DISK" 2>/dev/null | sed -n 's/.*PARTUUID="\([^"]*\)".*/\1/p')
        fi

        if $FILESYSTEM_FLAG; then
            FSTYPE=$(blkid "$DISK" 2>/dev/null | sed -n 's/.*TYPE="\([^"]*\)".*/\1/p')
        fi

        if $IS_RAID_FLAG; then
            IS_RAID="no"
            if [[ "$DISK" == /dev/md* ]] || mdadm --examine "$DISK" &>/dev/null; then
                IS_RAID="yes"
            fi
        fi

        if $IS_CRYPT_FLAG; then
            IS_CRYPT="no"
            if cryptsetup isLuks "$DISK" &>/dev/null; then
                IS_CRYPT="yes"
            fi
        fi

        if $TYPE_FLAG; then
            if [[ "$BASE_DISK" == nvme* ]]; then
                DISK_TYPE="nvme"
            else
                ROTATIONAL="/sys/block/$BASE_DISK/queue/rotational"
                if [ -f "$ROTATIONAL" ]; then
                    DISK_TYPE=$(cat "$ROTATIONAL") && [[ "$DISK_TYPE" == "0" ]] && DISK_TYPE="ssd" || DISK_TYPE="hdd"
                else
                    DISK_TYPE="unknown"
                fi
            fi
        fi

        # Construct output in fixed order
        OUTPUT=()
        OUTPUT+=("$NAME")             # volume
        OUTPUT+=("$DISK")             # disk
        OUTPUT+=("${MODEL:-}")        # model
        OUTPUT+=("${SERIAL:-}")       # serial
        OUTPUT+=("${HEALTH:-}")       # health
        OUTPUT+=("${TEMP:-}")         # temp
        OUTPUT+=("${DU_SIZE:-}")      # size
        OUTPUT+=("${USEP:-}")         # use_percent
        OUTPUT+=("${TOTAL:-}")        # total_space
        OUTPUT+=("${USED:-}")         # used_space
        OUTPUT+=("${AVAIL:-}")        # free_space
        OUTPUT+=("${PARTUUID:-}")     # partuuid
        OUTPUT+=("${FSTYPE:-}")       # filesystem
        OUTPUT+=("${IS_RAID:-}")      # is_raid
        OUTPUT+=("${IS_CRYPT:-}")     # is_crypt
        OUTPUT+=("${IS_MOUNTED:-}")   # is_mounted
        OUTPUT+=("${DISK_TYPE:-}")    # type

        # Print as pipe-delimited line
        echo "${OUTPUT[*]}" | sed 's/ /|/g'
    fi
done
