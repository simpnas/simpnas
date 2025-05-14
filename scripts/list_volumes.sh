#!/bin/bash

VOLUMES_DIR="/volumes"

# Declare all flags
declare -A FLAGS=(
    [volume]=false [disk]=false [model]=false [serial]=false
    [health]=false [temp]=false [size]=false [use_percent]=false
    [total_space]=false [used_space]=false [free_space]=false
    [partuuid]=false [filesystem]=false [is_raid]=false
    [is_crypt]=false [is_mounted]=false [type]=false
)

# Enable all if no args
if [ "$#" -eq 0 ]; then
    for key in "${!FLAGS[@]}"; do FLAGS[$key]=true; done
else
    for arg in "$@"; do
        [[ -v FLAGS[$arg] ]] && FLAGS[$arg]=true
    done
fi

# Loop through volumes
for volume in "$VOLUMES_DIR"/*; do
    [ -d "$volume" ] || continue

    # Initialize all output variables
    NAME=$(basename "$volume")
    DISK=""; MODEL=""; SERIAL=""; HEALTH=""; TEMP=""
    DU_SIZE=""; USEP=""; TOTAL=""; USED=""; AVAIL=""
    PARTUUID=""; FSTYPE=""; IS_RAID=""; IS_CRYPT=""
    IS_MOUNTED="no"; DISK_TYPE=""

    # Mount detection
    if mountpoint -q "$volume"; then
        IS_MOUNTED="yes"
        DISK=$(df "$volume" 2>/dev/null | awk 'NR==2 {print $1}')
    else
        FSTAB_ENTRY=$(awk -v vol="$volume" '$2 == vol {print $1}' /etc/fstab)
        if [[ "$FSTAB_ENTRY" =~ ^UUID= || "$FSTAB_ENTRY" =~ ^PARTUUID= ]]; then
            DISK=$(blkid -U "${FSTAB_ENTRY#*=}")
        elif [[ "$FSTAB_ENTRY" =~ ^/dev/ ]]; then
            DISK="$FSTAB_ENTRY"
        fi
    fi

    BASE_DISK=$(basename "$DISK" | sed 's/[0-9]*$//' | sed 's/p[0-9]*$//')

    # SMART preload
    if ${FLAGS[model]} || ${FLAGS[serial]} || ${FLAGS[temp]}; then
        SMART_INFO=$(smartctl -iA "$DISK" 2>/dev/null)
    fi

    # Conditional logic per field
    ${FLAGS[model]} && MODEL=$(echo "$SMART_INFO" | grep -i "Device Model" | awk -F': ' '{print $2}' | xargs)
    ${FLAGS[serial]} && SERIAL=$(echo "$SMART_INFO" | grep -i "Serial Number" | awk -F': ' '{print $2}' | xargs)
    ${FLAGS[health]} && HEALTH=$(smartctl -H "$DISK" 2>/dev/null | grep -i "SMART overall-health" | awk -F': ' '{print $2}' | xargs)
    ${FLAGS[temp]} && TEMP=$(echo "$SMART_INFO" | grep -i "Temperature_Celsius" | awk '{print $10}' | xargs)
    ${FLAGS[size]} && DU_SIZE=$(du -sh "$volume" 2>/dev/null | cut -f1)

    if ${FLAGS[use_percent]} || ${FLAGS[total_space]} || ${FLAGS[used_space]} || ${FLAGS[free_space]}; then
        read -r TOTAL USED AVAIL USEP <<< $(df -h --output=size,used,avail,pcent "$volume" 2>/dev/null | tail -n 1)
    fi

    ${FLAGS[partuuid]} && PARTUUID=$(blkid "$DISK" 2>/dev/null | sed -n 's/.*PARTUUID="\([^"]*\)".*/\1/p')
    ${FLAGS[filesystem]} && FSTYPE=$(blkid "$DISK" 2>/dev/null | sed -n 's/.*TYPE="\([^"]*\)".*/\1/p')

    if ${FLAGS[is_raid]}; then
        IS_RAID="no"
        if [[ "$DISK" == /dev/md* ]] || mdadm --examine "$DISK" &>/dev/null; then
            IS_RAID="yes"
        fi
    fi

    if ${FLAGS[is_crypt]}; then
        IS_CRYPT="no"
        cryptsetup isLuks "$DISK" &>/dev/null && IS_CRYPT="yes"
    fi

    if ${FLAGS[type]}; then
        if [[ "$BASE_DISK" == nvme* ]]; then
            DISK_TYPE="nvme"
        else
            ROTATIONAL="/sys/block/$BASE_DISK/queue/rotational"
            if [ -f "$ROTATIONAL" ]; then
                [ "$(cat "$ROTATIONAL")" == "0" ] && DISK_TYPE="ssd" || DISK_TYPE="hdd"
            else
                DISK_TYPE="unknown"
            fi
        fi
    fi

    # Output all fields in fixed order
    echo "$NAME|$DISK|$MODEL|$SERIAL|$HEALTH|$TEMP|$DU_SIZE|$USEP|$TOTAL|$USED|$AVAIL|$PARTUUID|$FSTYPE|$IS_RAID|$IS_CRYPT|$IS_MOUNTED|$DISK_TYPE"
done
