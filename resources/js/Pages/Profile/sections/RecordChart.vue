<template>
    <div class="card kpi-card">
        <h5 class="card-title kpi-title mt-3 mb-0 text-uppercase text-muted small">
            <div class="kpi-label">Total de</div>
            <div class="kpi-period">
                <span v-if="type === 'day'">HOY</span>
                <span v-else-if="type === 'week'">SEMANA</span>
            </div>
        </h5>

        <!-- Card Body -->
        <div class="card-body text-center">
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

                    <!-- progreso -->
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
        type: { type: String, required: true },
        hoursWorked: { type: Number, required: true },
        horario: { type: Number, required: true },
        progressColor: { type: String, default: '#4181f2' },
        pauseColor: { type: String, default: '#ff6b6b' },
        navegable: { type: Boolean, default: false } // 👈 NUEVA PROP
    },
    data() {
        return {
            fullCircle: 180,
            animatedProgress: 0,
        };
    },
    computed: {
        expectedWorkedHours() {
            return this.horario;
        },
        expectedWorkedHoursText() {
            const h = Math.floor(this.expectedWorkedHours);
            const m = Math.round((this.expectedWorkedHours - h) * 60);
            if (m === 0) return `${h}h`;
            if (h === 0) return `${m}m`;
            return `${h}h ${m}m`;
        },
        formattedWorkedHours() {
            const h = Math.floor(this.hoursWorked);
            const m = Math.round((this.hoursWorked - h) * 60);
            return `${h}h ${m}m`;
        },
        progressRatio() {
            return Math.min(this.hoursWorked / this.expectedWorkedHours, 1);
        },
        progressPercent() {
            return this.progressRatio * this.fullCircle;
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
</style>
