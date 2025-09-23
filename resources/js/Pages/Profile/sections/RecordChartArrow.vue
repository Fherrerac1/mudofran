<template>
    <div div class="card kpi-card">
        <!-- Flechas de navegación -->
        <div class="d-flex justify-content-between align-items-center p-3">
            <button class="btn btn-icon toggle-btn" @click="toggleType">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-icon toggle-btn" @click="toggleType">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Título -->
        <div class="kpi-title-wrapper text-center my-1">
            <div class="kpi-label">TOTAL DE</div>
            <div class="kpi-period">
                <span v-if="currentType === 'day'">HOY</span>
                <span v-else>SEMANA</span>
            </div>
        </div>

        <!-- SVG progreso -->
        <div class="card-body text-center ">
        <div class="kpi--progress fade-in">
            <svg ref="svgRef" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 135 105">
            <!-- fondo blanco -->
            <g stroke="none" stroke-width="1" fill="none" stroke-linecap="round">
                <g transform="translate(-401.000000, -196.000000)" stroke="white" stroke-width="16">
                <g transform="translate(409.000000, 204.000000)">
                    <path
                    d="M8.24024635,87 C3.01001579,78.3501504 0,68.2074729 0,57.3617686
                        C0,25.6817386 25.6817386,0 57.3617686,0
                        C89.0417986,0 114.723537,25.6817386 114.723537,57.3617686
                        C114.723537,68.2074729 111.713521,78.3501504 106.483291,87">
                    </path>
                </g>
                </g>
            </g>

            <!-- fondo gris -->
            <g stroke="none" stroke-width="1" fill="none" stroke-linecap="round">
                <g transform="translate(-401.000000, -196.000000)" stroke="#edeff3" stroke-width="16">
                <g transform="translate(409.000000, 204.000000)">
                    <path
                    d="M8.24024635,87 C3.01001579,78.3501504 0,68.2074729 0,57.3617686
                        C0,25.6817386 25.6817386,0 57.3617686,0
                        C89.0417986,0 114.723537,25.6817386 114.723537,57.3617686
                        C114.723537,68.2074729 111.713521,78.3501504 106.483291,87">
                    </path>
                </g>
                </g>
            </g>

            <!-- progreso dinámico -->
            <g stroke="none" stroke-width="1" fill="none" stroke-linecap="round">
                <g transform="translate(-401.000000, -196.000000)" stroke-width="16">
                <g transform="translate(409.000000, 204.000000)">
                    <path
                    ref="progressPath"
                    d="M8.24024635,87 C3.01001579,78.3501504 0,68.2074729 0,57.3617686
                        C0,25.6817386 25.6817386,0 57.3617686,0
                        C89.0417986,0 114.723537,25.6817386 114.723537,57.3617686
                        C114.723537,68.2074729 111.713521,78.3501504 106.483291,87"
                    :style="{
                        strokeDasharray: `${animatedProgress}, ${fullCircle}`,
                        strokeDashoffset: 0,
                        stroke: progressColor,
                    }"
                    />
                </g>
                </g>
            </g>
            </svg>

            <!-- leyenda -->
            <div class="progress__legend">
                <div class="legend-worked">{{ formattedWorkedHours }}</div>
                <div class="legend-expected">/ {{ expectedWorkedHoursText }}</div>
            </div>
        </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        hoursWorkedDay: { type: Number, required: true },
        hoursWorkedWeek: { type: Number, required: true },
        horarioDay: { type: Number, required: true },
        horarioWeek: { type: Number, required: true },
        progressColor: { type: String, default: '#4181f2' },
    },
    data() {
        return {
        fullCircle: 180,
        animatedProgress: 0,
        currentType: 'day',
        };
    },
    computed: {
        effectiveWorked() {
            return this.currentType === 'day' ? this.hoursWorkedDay : this.hoursWorkedWeek;
        },
        effectiveHorario() {
            return this.currentType === 'day' ? this.horarioDay : this.horarioWeek;
        },
        formattedWorkedHours() {
            const h = Math.floor(this.effectiveWorked);
            const m = Math.round((this.effectiveWorked - h) * 60);
            return `${h}h ${m}m`;
        },
        expectedWorkedHoursText() {
            const h = Math.floor(this.effectiveHorario);
            const m = Math.round((this.effectiveHorario - h) * 60);
            if (m === 0) return `${h}h`;
            if (h === 0) return `${m}m`;
            return `${h}h ${m}m`;
        },
        progressPercent() {
            return Math.min(this.effectiveWorked / this.effectiveHorario, 1) * this.fullCircle;
        },
    },
    mounted() {
        const path = this.$refs.progressPath;
        if (path) {
            this.fullCircle = path.getTotalLength();
        }
        this.animateProgress();
    },
    methods: {
        toggleType() {
            this.currentType = this.currentType === 'day' ? 'week' : 'day';
            this.animateProgress();
        },
        animateProgress() {
            const target = this.progressPercent;
            const duration = 1000;
            const start = performance.now();

            const step = (timestamp) => {
                const elapsed = timestamp - start;
                const progress = Math.min(elapsed / duration, 1);
                this.animatedProgress = progress * target;

                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };

        requestAnimationFrame(step);
        },
    },
};
</script>

<style scoped>
/*=============================================
=            Tarjeta limpia y moderna         =
=============================================*/
.kpi-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 300px; /* altura uniforme */
    height: 100%;
}

.kpi-title {
    font-size: 1.3rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 0;
    color: var(--unique-color, #4181f2);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-label {
    font-size: 0.85rem;
    color: #777;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: 500;
}

.kpi-period {
    font-size: 1.3rem;
    font-weight: 700;
    background: var(--gradient-color-unique, linear-gradient(45deg, #6454ff, #4181f2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/*=============================================
=                Leyenda central              =
=============================================*/
.progress__legend {
    position: absolute;
    top: 75%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #76a9ff;
}

.legend-worked {
    font-size: 1.3rem;
    font-weight: 700;
}

.legend-expected {
    font-size: 1.1rem;
    color: #999;
    margin-top: 0.25rem;
}


.kpi--progress svg {
    width: 100%;
    height: auto;
    max-width: 280px;
    margin: 0 auto;
}

.toggle-btn {
    background: transparent;
    border: none;
    padding: 4px 6px;
    border-radius: 4px;
    color: #ccc;
    font-size: 0.75rem;
    transition: transform 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}

.toggle-btn:hover {
    background-color: rgba(0, 0, 0, 0.05);
    transform: scale(1.1);
    color: #333;
}

</style>
