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

    # Detect NVMe or not
    IS_NVME=false
    [[ "$DISK" == nvme* ]] && IS_NVME=true

    # SMART support
    SMART_SUPPORTED="no"
    if $IS_NVME; then
        SMART_SUPPORTED="yes"
    else
        smartctl -i "$DEV" 2>/dev/null | grep -qi "SMART support is: Available" && SMART_SUPPORTED="yes"
    fi
    ${FLAGS[has_smart]} && HAS_SMART="$SMART_SUPPORTED"

    # Get SMART data if needed
    if ${FLAGS[vendor]} || ${FLAGS[serial]} || ${FLAGS[temp]} || ${FLAGS[health]} || ${FLAGS[bad_blocks]} || ${FLAGS[power_on]} || ${FLAGS[power_cycle]}; then
        SMART_INFO=$(smartctl -a "$DEV" 2>/dev/null)
    fi

    ${FLAGS[vendor]} && VENDOR=$(echo "$SMART_INFO" | grep -E 'Model Family|Device Model|Model Number' | head -n1 | cut -d: -f2- | xargs)
    ${FLAGS[serial]} && SERIAL=$(echo "$SMART_INFO" | grep -i 'Serial Number' | cut -d: -f2- | xargs)
    ${FLAGS[size]} && SIZE=$(lsblk -dn -o size "$DEV" 2>/dev/null | xargs)

    if ${FLAGS[type]}; then
        if $IS_NVME; then
            TYPE="NVMe"
        else
            ROTATION=$(smartctl -i "$DEV" 2>/dev/null | grep -i 'Rotation Rate' | awk -F: '{print $2}' | xargs)
            if [[ "$ROTATION" =~ ([57]200 rpm) ]]; then
                TYPE="HDD"
            elif [[ "$ROTATION" == "Solid State Device" ]]; then
                TYPE="SSD"
            fi
        fi
    fi

    if ${FLAGS[health]}; then
        if $IS_NVME; then
            WARNING_HEX=$(echo "$SMART_INFO" | awk '/Critical Warning/ {print $3}' | head -n1)
            if [[ "$WARNING_HEX" == "0x00" || "$WARNING_HEX" == "0" ]]; then
                HEALTH="PASSED"
            else
                HEALTH="FAILED"
            fi
        else
            HEALTH=$(echo "$SMART_INFO" | grep -i "SMART overall-health" | awk -F: '{print $2}' | xargs)
        fi
    fi

    if ${FLAGS[temp]}; then
        if $IS_NVME; then
            TEMP=$(echo "$SMART_INFO" | awk -F: '/Temperature:/ {print $2}' | xargs)
            [[ -z "$TEMP" ]] && TEMP=$(echo "$SMART_INFO" | awk '/Composite Temperature/ {print $4}' | xargs)
        else
            TEMP=$(echo "$SMART_INFO" | awk '/Temperature_Celsius|Temperature_Internal/ {print $NF}' | head -n1 | xargs)
        fi
    fi

    ${FLAGS[bad_blocks]} && BAD_BLOCKS=$(echo "$SMART_INFO" | awk '/Reallocated_Sector_Ct/ {print $10}' | xargs)
    ${FLAGS[power_on]} && POWER_ON=$(echo "$SMART_INFO" | awk '/Power_On_Hours/ || /Power On Hours/ {print $NF}' | head -n1 | xargs)
    ${FLAGS[power_cycle]} && POWER_CYCLE=$(echo "$SMART_INFO" | awk '/Power_Cycle_Count/ || /Power Cycles/ {print $NF}' | head -n1 | xargs)

    # Fixed-order output
    echo "$NAME|$VENDOR|$SERIAL|$SIZE|$TYPE|$HAS_SMART|$HEALTH|$TEMP|$BAD_BLOCKS|$POWER_ON|$POWER_CYCLE"
done
