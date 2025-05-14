#!/bin/bash

# Declare all field flags (default: false)
declare -A FLAGS=(
    [name]=false
    [vendor]=false
    [serial]=false
    [size]=false
    [type]=false
    [has_smart]=false
    [health]=false
    [temp]=false
    [bad_blocks]=false
    [power_on]=false
    [power_cycle]=false
)

# If no arguments passed, enable all fields
if [ "$#" -eq 0 ]; then
    for key in "${!FLAGS[@]}"; do FLAGS[$key]=true; done
else
    for arg in "$@"; do
        [[ -v FLAGS[$arg] ]] && FLAGS[$arg]=true
    done
fi

# Enumerate disks using lsblk
lsblk -dn -o KNAME,TYPE | awk '$2 == "disk" {print $1}' | while read -r DISK; do
    DEV="/dev/$DISK"

    # Initialize all fields
    NAME="$DISK"
    VENDOR=""
    SERIAL=""
    SIZE=""
    TYPE="unknown"
    HAS_SMART=""
    HEALTH=""
    TEMP=""
    BAD_BLOCKS=""
    POWER_ON=""
    POWER_CYCLE=""

    # SMART support check
    SMART_SUPPORTED="no"
    smartctl -i "$DEV" 2>/dev/null | grep -qi "SMART support is: Available" && SMART_SUPPORTED="yes"
    ${FLAGS[has_smart]} && HAS_SMART="$SMART_SUPPORTED"

    # Preload smartctl info if needed
    if ${FLAGS[vendor]} || ${FLAGS[serial]} || ${FLAGS[temp]} || ${FLAGS[health]} || ${FLAGS[bad_blocks]} || ${FLAGS[power_on]} || ${FLAGS[power_cycle]}; then
        SMART_INFO=$(smartctl -iA "$DEV" 2>/dev/null)
    fi

    ${FLAGS[vendor]} && VENDOR=$(echo "$SMART_INFO" | grep -E 'Model Family|Device Model|Model Number' | head -n1 | cut -d: -f2- | xargs)
    ${FLAGS[serial]} && SERIAL=$(echo "$SMART_INFO" | grep -i 'Serial Number' | cut -d: -f2- | xargs)
    ${FLAGS[size]} && SIZE=$(lsblk -dn -o size "$DEV" 2>/dev/null | xargs)

    if ${FLAGS[type]}; then
        ROTATION=$(smartctl -i "$DEV" 2>/dev/null | grep -i 'Rotation Rate' | awk -F: '{print $2}' | xargs)
        if [[ "$ROTATION" =~ ([57]200 rpm) ]]; then
            TYPE="HDD"
        elif [[ "$ROTATION" == "Solid State Device" ]]; then
            TYPE="SSD"
        elif [[ "$DISK" == nvme* ]]; then
            TYPE="NVMe"
        fi
    fi

    ${FLAGS[health]} && HEALTH=$(smartctl -H "$DEV" 2>/dev/null | grep -i "SMART overall-health" | awk -F: '{print $2}' | xargs)
    ${FLAGS[temp]} && TEMP=$(echo "$SMART_INFO" | awk '/Temperature_Celsius|Composite Temperature|Temperature_Internal/ {print $NF}' | head -n1 | xargs)
    ${FLAGS[bad_blocks]} && BAD_BLOCKS=$(echo "$SMART_INFO" | awk '/Reallocated_Sector_Ct/ {print $10}' | xargs)
    ${FLAGS[power_on]} && POWER_ON=$(echo "$SMART_INFO" | awk '/Power_On_Hours/ {print $10}' | xargs)
    ${FLAGS[power_cycle]} && POWER_CYCLE=$(echo "$SMART_INFO" | awk '/Power_Cycle_Count/ {print $10}' | xargs)

    # Fixed-order output
    echo "$NAME|$VENDOR|$SERIAL|$SIZE|$TYPE|$HAS_SMART|$HEALTH|$TEMP|$BAD_BLOCKS|$POWER_ON|$POWER_CYCLE"
done
