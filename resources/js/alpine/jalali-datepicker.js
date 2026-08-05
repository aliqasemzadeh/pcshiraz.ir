import {
    compareJalali,
    formatJalali,
    jalaaliMonthLength,
    jalaliKey,
    parseJalali,
    todayJalali,
    weekdayOfJalali,
} from '../jalali-calendar.js';

export default (config = {}) => ({
    open: false,
    value: config.value ?? '',
    viewYear: todayJalali().jy,
    viewMonth: todayJalali().jm,
    minDate: null,
    maxDate: null,
    disabled: Boolean(config.disabled),
    readonly: Boolean(config.readonly),
    monthNames: config.monthNames ?? [],
    weekdayNames: config.weekdayNames ?? [],
    todayLabel: config.todayLabel ?? 'Today',
    clearLabel: config.clearLabel ?? 'Clear',
    wireProperty: config.wireProperty ?? null,

    init() {
        this.minDate = this.resolveBoundary(config.minDate);
        this.maxDate = this.resolveBoundary(config.maxDate);
        this.syncFromInput();
        this.resetView();

        if (this.wireProperty) {
            this.$wire.$watch(this.wireProperty, (next) => {
                this.value = next ?? '';
                this.resetView();
            });
        }
    },

    resolveBoundary(boundary) {
        if (! boundary) {
            return null;
        }

        if (boundary === 'today') {
            return todayJalali();
        }

        return parseJalali(boundary);
    },

    syncFromInput() {
        if (this.$refs.input) {
            this.value = this.$refs.input.value ?? '';
        }
    },

    resetView() {
        const parsed = parseJalali(this.value);

        if (parsed) {
            this.viewYear = parsed.jy;
            this.viewMonth = parsed.jm;

            return;
        }

        const fallback = this.maxDate ?? todayJalali();
        this.viewYear = fallback.jy;
        this.viewMonth = fallback.jm;
    },

    get selectedDate() {
        return parseJalali(this.value);
    },

    get displayValue() {
        return this.value;
    },

    get monthLabel() {
        const name = this.monthNames[this.viewMonth - 1] ?? this.viewMonth;

        return `${name} ${this.viewYear}`;
    },

    get years() {
        const current = todayJalali().jy;
        const start = (this.minDate?.jy ?? current - 120);
        const end = (this.maxDate?.jy ?? current + 10);
        const years = [];

        for (let year = end; year >= start; year -= 1) {
            years.push(year);
        }

        return years;
    },

    get calendarDays() {
        const days = [];
        const firstWeekday = weekdayOfJalali(this.viewYear, this.viewMonth, 1);
        const monthLength = jalaaliMonthLength(this.viewYear, this.viewMonth);
        const prevMonth = this.viewMonth === 1 ? 12 : this.viewMonth - 1;
        const prevYear = this.viewMonth === 1 ? this.viewYear - 1 : this.viewYear;
        const prevMonthLength = jalaaliMonthLength(prevYear, prevMonth);

        for (let i = firstWeekday - 1; i >= 0; i -= 1) {
            const jd = prevMonthLength - i;

            days.push(this.makeDay(prevYear, prevMonth, jd, false));
        }

        for (let jd = 1; jd <= monthLength; jd += 1) {
            days.push(this.makeDay(this.viewYear, this.viewMonth, jd, true));
        }

        let nextMonthDay = 1;
        const nextMonth = this.viewMonth === 12 ? 1 : this.viewMonth + 1;
        const nextYear = this.viewMonth === 12 ? this.viewYear + 1 : this.viewYear;

        while (days.length % 7 !== 0) {
            days.push(this.makeDay(nextYear, nextMonth, nextMonthDay, false));
            nextMonthDay += 1;
        }

        return days;
    },

    makeDay(jy, jm, jd, inMonth) {
        const date = { jy, jm, jd };
        const key = jalaliKey(date);
        const selected = this.selectedDate ? jalaliKey(this.selectedDate) === key : false;
        const today = jalaliKey(todayJalali()) === key;
        const disabled = ! this.isDateAllowed(date);

        return {
            key,
            jy,
            jm,
            jd,
            label: jd,
            inMonth,
            selected,
            today,
            disabled,
        };
    },

    isDateAllowed(date) {
        if (this.minDate && compareJalali(date, this.minDate) < 0) {
            return false;
        }

        if (this.maxDate && compareJalali(date, this.maxDate) > 0) {
            return false;
        }

        return true;
    },

    canOpen() {
        return ! this.disabled && ! this.readonly;
    },

    toggle() {
        if (! this.canOpen()) {
            return;
        }

        this.open = ! this.open;

        if (this.open) {
            this.resetView();
        }
    },

    close() {
        this.open = false;
    },

    prevMonth() {
        if (this.viewMonth === 1) {
            this.viewMonth = 12;
            this.viewYear -= 1;
        } else {
            this.viewMonth -= 1;
        }
    },

    nextMonth() {
        if (this.viewMonth === 12) {
            this.viewMonth = 1;
            this.viewYear += 1;
        } else {
            this.viewMonth += 1;
        }
    },

    selectDate(day) {
        if (day.disabled) {
            return;
        }

        this.value = formatJalali(day);
        this.$refs.input.value = this.value;
        this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
    },

    selectToday() {
        const today = todayJalali();

        if (! this.isDateAllowed(today)) {
            return;
        }

        this.selectDate(today);
    },

    clear() {
        this.value = '';
        this.$refs.input.value = '';
        this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.close();
    },
});
