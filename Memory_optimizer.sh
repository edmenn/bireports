#!/bin/bash
# ============================================================
# MEMORY OPTIMIZATION SCRIPT FOR DOCKER SERVERS
# Author: ChatGPT (for Edgar Menna - birentable)
# Environment: Debian (DigitalOcean)
# ============================================================

echo "🚀 Starting memory optimization..."

# ============================================================
# 🔧 CONFIGURATION VARIABLES — adjust these for future upgrades
# ============================================================

# --- SWAP CONFIGURATION ---
SWAP_SIZE_GB=4                 # Total swap file size (in GB)

# --- DEFAULT CONTAINER LIMITS ---
DEFAULT_RAM_LIMIT="512m"       # RAM limit for regular containers
DEFAULT_SWAP_LIMIT="1g"        # Total memory (RAM + swap) for regular containers

# --- POSTGRESQL SPECIAL LIMITS ---
PG_RAM_LIMIT="768m"            # RAM limit for PostgreSQL
PG_SWAP_LIMIT="1536m"          # Total memory (RAM + swap) for PostgreSQL

# --- KERNEL MEMORY TUNING ---
SWAPPINESS=10
CACHE_PRESSURE=50

# ============================================================
# 1️⃣  Create and enable swap if missing
# ============================================================
if ! grep -q '/swapfile' /etc/fstab; then
    echo "🧠 Creating ${SWAP_SIZE_GB}GB swap file..."
    fallocate -l "${SWAP_SIZE_GB}G" /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
else
    echo "✅ Swap already configured in /etc/fstab"
fi

# ============================================================
# 2️⃣  Tune kernel memory behavior
# ============================================================
echo "⚙️ Adjusting kernel parameters..."
sysctl -w vm.swappiness=$SWAPPINESS
sysctl -w vm.vfs_cache_pressure=$CACHE_PRESSURE

grep -q 'vm.swappiness' /etc/sysctl.conf || echo "vm.swappiness=$SWAPPINESS" >> /etc/sysctl.conf
grep -q 'vm.vfs_cache_pressure' /etc/sysctl.conf || echo "vm.vfs_cache_pressure=$CACHE_PRESSURE" >> /etc/sysctl.conf

# ============================================================
# 3️⃣  Configure Docker defaults
# ============================================================
echo "🐋 Setting Docker global limits..."
mkdir -p /etc/docker
cat > /etc/docker/daemon.json <<EOF
{
  "default-address-pools": [
    {"base": "172.17.0.0/16", "size": 24}
  ],
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "10m",
    "max-file": "3"
  },
  "default-ulimits": {
    "nofile": {"Name": "nofile", "Hard": 64000, "Soft": 64000}
  },
  "init": true
}
EOF

systemctl daemon-reexec
systemctl restart docker

# ============================================================
# 4️⃣  Apply memory limits to running containers
# ============================================================
echo "📦 Applying memory limits to running containers..."
for c in $(docker ps -q); do
    name=$(docker inspect --format '{{.Name}}' "$c" | sed 's|/||')

    if [[ "$name" == "pgsql" ]]; then
        echo "   🐘 PostgreSQL detected → Limit: $PG_RAM_LIMIT RAM / $PG_SWAP_LIMIT total"
        docker update --memory "$PG_RAM_LIMIT" --memory-swap "$PG_SWAP_LIMIT" "$c"
    else
        echo "   → Updating container $name ($DEFAULT_RAM_LIMIT RAM / $DEFAULT_SWAP_LIMIT total)..."
        docker update --memory "$DEFAULT_RAM_LIMIT" --memory-swap "$DEFAULT_SWAP_LIMIT" "$c"
    fi
done

# ============================================================
# 5️⃣  Display summary
# ============================================================
echo
echo "✅ Optimization complete."
swapon --show
free -h
docker info | grep -E 'Memory|Swap'
echo
echo "ℹ️ Regular containers limited to $DEFAULT_RAM_LIMIT RAM / $DEFAULT_SWAP_LIMIT total."
echo "ℹ️ PostgreSQL limited to $PG_RAM_LIMIT RAM / $PG_SWAP_LIMIT total."
echo
echo "💡 Run 'docker stats' to monitor real-time memory usage."
