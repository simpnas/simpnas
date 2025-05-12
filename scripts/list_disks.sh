#!/bin/bash

# Field flags
NAME_FLAG=false
VENDOR_FLAG=false
SERIAL_FLAG=false
SIZE_FLAG=false
TYPE_FLAG=false
SMART_FLAG=false
HEALTH_FLAG=false
TEMP_FLAG=false
BAD_BLOCKS_FLAG=false
POWER_ON_FLAG=false
POWER_CYCLE_FLAG=false

# Enable all fields if no arguments
if [ "$#" -eq 0 ]; then
    NAME_FLAG=true
    VENDOR_FLAG=true
    SERIAL_FLAG=true
    SIZE_FLAG=true
    TYPE_FLAG=true
    SMART_FLAG=true
    HEALTH_FLAG=true
    TEMP_FLAG=true
    BAD_BLOCKS_FLAG=true
    POWER_ON_FLAG=true
    POWER_CYCLE_FLAG=true
fi

# Parse field args
for arg in "$@"; do
    case "$arg" in
        name) NAME_FLAG=true ;;
        vendor) VENDOR_FLAG=true ;;
        serial) SERIAL_FLAG=true ;;
        size) SIZE_FLAG=true ;;
        type) TYPE_FLAG=true ;;
        has_smart) SMART_FLAG=true ;;
        health) HEALTH_FLAG=true ;;
        temp) TEMP_FLAG=true ;;
        bad_blocks) BAD_BLOCKS_FLAG=true ;;
        power_on) POWER_ON_FLAG=true ;;
        power_cycle) POWER_CYCLE_FLAG=true ;;
    esac
done

# Loop through each disk device
lsblk -dn -o KNAME,TYPE | awk '$2 == "disk" {print $1}' | while read -r DISK; do
    NAME="$DISK"
    VENDOR=""
    SERIAL=""
    SIZE=""
    TYPE="unknown"
    HAS_SMART="no"
    HEALTH=""
    TEMP=""
    BAD_BLOCKS=""
    POWER_ON=""
    POWER_CYCLE=""

    DEV_PATH="/dev/$DISK"

    # SMART available?
    smartctl -i "$DEV_PATH" 2>/dev/null | grep -qi "SMART support is: Available" && HAS_SMART="yes"

    # Vendor/Model
    if $VENDOR_FLAG; then
        VENDOR=$(smartctl -i "$DEV_PATH" 2>/dev/null | grep -E 'Model Family|Device Model|Model Number' | head -n1 | cut -d: -f2- | xargs)
        [ -z "$VENDOR" ] && VENDOR=$(lsblk -dn -o vendor "$DEV_PATH" 2>/dev/null | head -n1 | xargs)
    fi

    # Serial
    if $SERIAL_FLAG; then
        SERIAL=$(smartctl -i "$DEV_PATH" 2>/dev/null | grep -i 'Serial Number' | cut -d: -f2- | xargs)
    fi

    # Size
    if $SIZE_FLAG; then
        SIZE=$(lsblk -dn -o size "$DEV_PATH" 2>/dev/null | xargs)
    fi

    # Type (based on rotation or name)
    if $TYPE_FLAG; then
        ROTATION=$(smartctl -i "$DEV_PATH" 2>/dev/null | grep -i 'Rotation Rate' | awk -F: '{print $2}' | xargs)
        if [[ "$ROTATION" =~ ([57]200 rpm) ]]; then
            TYPE="HDD"
        elif [[ "$ROTATION" == "Solid State Device" ]]; then
            TYPE="SSD"
        elif [[ "$NAME" == nvme* ]]; then
            TYPE="NVMe"
        fi
    fi

    # Health status
    if $HEALTH_FLAG; then
        HEALTH=$(smartctl -H "$DEV_PATH" 2>/dev/null | grep -i "SMART overall-health" | awk -F: '{print $2}' | xargs)
    fi

    # Temperature
    if $TEMP_FLAG; then
        TEMP=$(smartctl -A "$DEV_PATH" 2>/dev/null | awk '/Temperature_Celsius|Composite Temperature|Temperature_Internal/ {print $NF}' | head -n1 | xargs)
    fi

    # Reallocated sector count (bad blocks)
    if $BAD_BLOCKS_FLAG; then
        BAD_BLOCKS=$(smartctl -A "$DEV_PATH" 2>/dev/null | awk '/Reallocated_Sector_Ct/ {print $10}' | xargs)
    fi

    # Power-on hours
    if $POWER_ON_FLAG; then
        POWER_ON=$(smartctl -A "$DEV_PATH" 2>/dev/null | awk '/Power_On_Hours/ {print $10}' | xargs)
    fi

    # Power cycle count
    if $POWER_CYCLE_FLAG; then
        POWER_CYCLE=$(smartctl -A "$DEV_PATH" 2>/dev/null | awk '/Power_Cycle_Count/ {print $10}' | xargs)
    fi

    # Fixed-order output (adjust field count in PHP if you change this)
    OUT=()
    OUT+=("$NAME")
    OUT+=("${VENDOR:-}")
    OUT+=("${SERIAL:-}")
    OUT+=("${SIZE:-}")
    OUT+=("${TYPE:-}")
    OUT+=("${HAS_SMART:-}")
    OUT+=("${HEALTH:-}")
    OUT+=("${TEMP:-}")
    OUT+=("${BAD_BLOCKS:-}")
    OUT+=("${POWER_ON:-}")
    OUT+=("${POWER_CYCLE:-}")

    IFS='|'; echo "${OUT[*]}"; unset IFS
done
