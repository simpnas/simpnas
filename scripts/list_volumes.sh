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

# Check if arguments are provided
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
    esac
done

# Loop through each volume
for volume in "$VOLUMES_DIR"/*; do
    if [ -d "$volume" ]; then
        NAME=$(basename "$volume")

        if $VOLUME_FLAG; then
            echo -n "$NAME|"
        fi
        
        # Get disk info (if requested)
        DISK=$(df "$volume" 2>/dev/null | awk 'NR==2 {print $1}')
        if $DISK_FLAG; then
            echo -n "$DISK|"
        fi

        # Get disk model (if requested)
        if $MODEL_FLAG; then
            MODEL=$(smartctl -i "$DISK" 2>/dev/null | grep -i "Device Model" | awk -F': ' '{print $2}')
            echo -n "$MODEL|"
        fi
        
        # Get disk serial (if requested)
        if $SERIAL_FLAG; then
            SERIAL=$(smartctl -i "$DISK" 2>/dev/null | grep -i "Serial Number" | awk -F': ' '{print $2}')
            echo -n "$SERIAL|"
        fi

        # Get disk health (if requested)
        if $HEALTH_FLAG; then
            HEALTH=$(smartctl -H "$DISK" 2>/dev/null | grep -i "SMART overall-health" | awk -F': ' '{print $2}' | xargs)
            echo -n "$HEALTH|"
        fi

        # Get disk temperature (if requested)
        if $TEMP_FLAG; then
            TEMP=$(smartctl -A "$DISK" 2>/dev/null | grep -i "Temperature_Celsius" | awk '{print $10}' | xargs)
            echo -n "$TEMP|"
        fi

        # Get directory size (if requested)
        if $SIZE_FLAG; then
            DU_SIZE=$(du -sh "$volume" 2>/dev/null | cut -f1)
            echo -n "$DU_SIZE|"
        fi
        
        # Get space usage (if requested)
        if $USE_PERCENT_FLAG || $TOTAL_SPACE_FLAG || $USED_SPACE_FLAG || $FREE_SPACE_FLAG; then
            read -r TOTAL USED AVAIL USEP <<< $(df -H "$volume" 2>/dev/null | awk 'NR==2 {print $2, $3, $4, $5}')
            if $TOTAL_SPACE_FLAG; then
                echo -n "$TOTAL|"
            fi
            if $USED_SPACE_FLAG; then
                echo -n "$USED|"
            fi
            if $FREE_SPACE_FLAG; then
                echo -n "$AVAIL|"
            fi
            if $USE_PERCENT_FLAG; then
                echo -n "$USEP|"
            fi
        fi

        echo
    fi
done
