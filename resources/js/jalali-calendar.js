const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];

const div = (a, b) => ~~(a / b);

const mod = (a, b) => a - ~~(a / b) * b;

const jalCal = (jy) => {
    const bl = breaks.length;
    let gy = jy + 621;
    let leapJ = -14;
    let jp = breaks[0];
    let jm;
    let jump;
    let leap;
    let leapG;
    let march;
    let n;
    let i;

    if (jy < jp || jy >= breaks[bl - 1]) {
        throw new Error(`Invalid Jalaali year ${jy}`);
    }

    for (i = 1; i < bl; i += 1) {
        jm = breaks[i];
        jump = jm - jp;

        if (jy < jm) {
            break;
        }

        leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
        jp = jm;
    }

    n = jy - jp;
    leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);

    if (mod(jump, 33) === 4 && jump - n === 4) {
        leapJ += 1;
    }

    leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
    march = 20 + leapJ - leapG;

    if (jump - n < 6) {
        n = n - jump + div(jump + 4, 33) * 33;
    }

    leap = mod(mod(n + 1, 33) - 1, 4);

    if (leap === -1) {
        leap = 4;
    }

    return { leap, gy, march };
};

export const jalaaliMonthLength = (jy, jm) => {
    if (jm <= 6) {
        return 31;
    }

    if (jm <= 11) {
        return 30;
    }

    return jalCal(jy).leap === 0 ? 30 : 29;
};

export const toJalaali = (gy, gm, gd) => {
    const gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    let jy;

    if (gy <= 1600) {
        jy = 0;
        gy -= 621;
    } else {
        jy = 979;
        gy -= 1600;
    }

    let gy2 = gm > 2 ? gy + 1 : gy;
    let days = 365 * gy + div((gy2 + 3), 4) - div((gy2 + 99), 100) + div((gy2 + 399), 400) - 80 + gd + gdm[gm - 1];
    jy += 33 * div(days, 12053);
    days %= 12053;
    jy += 4 * div(days, 1461);
    days %= 1461;

    if (days > 365) {
        jy += div(days - 1, 365);
        days = (days - 1) % 365;
    }

    let jm;
    let jd;

    if (days < 186) {
        jm = 1 + div(days, 31);
        jd = 1 + mod(days, 31);
    } else {
        jm = 7 + div(days - 186, 30);
        jd = 1 + mod(days - 186, 30);
    }

    return { jy, jm, jd };
};

export const toGregorian = (jy, jm, jd) => {
    const { gy, march } = jalCal(jy);
    let gDayNo = 365 * jy + div((jy + 9), 33) * 8 + div(mod((jy + 9), 33) + 3, 4);

    for (let i = 0; i < jm - 1; i += 1) {
        gDayNo += jalaaliMonthLength(jy, i + 1);
    }

    gDayNo += jd - 1;

    let gYear = gy;
    let gDayCount = 365 * gYear + div(gYear, 4) - div(gYear, 100) + div(gYear, 400);

    while (gDayNo >= gDayCount) {
        gDayCount = 365 * (gYear + 1) + div(gYear + 1, 4) - div(gYear + 1, 100) + div(gYear + 1, 400);
        gYear += 1;
    }

    let gDay = gDayNo - (365 * gYear + div(gYear, 4) - div(gYear, 100) + div(gYear, 400));
    const salA = [0, 31, (gYear % 4 === 0 && gYear % 100 !== 0) || gYear % 400 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm;

    for (gm = 1; gm <= 12 && gDay >= salA[gm]; gm += 1) {
        gDay -= salA[gm];
    }

    return { gy: gYear, gm, gd: gDay + 1 };
};

export const parseJalali = (value) => {
    if (! value || typeof value !== 'string') {
        return null;
    }

    const match = value.trim().match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);

    if (! match) {
        return null;
    }

    const jy = Number(match[1]);
    const jm = Number(match[2]);
    const jd = Number(match[3]);

    if (jm < 1 || jm > 12 || jd < 1 || jd > jalaaliMonthLength(jy, jm)) {
        return null;
    }

    return { jy, jm, jd };
};

export const formatJalali = ({ jy, jm, jd }) => `${jy}/${String(jm).padStart(2, '0')}/${String(jd).padStart(2, '0')}`;

export const todayJalali = () => {
    const now = new Date();

    return toJalaali(now.getFullYear(), now.getMonth() + 1, now.getDate());
};

export const compareJalali = (a, b) => {
    if (a.jy !== b.jy) {
        return a.jy - b.jy;
    }

    if (a.jm !== b.jm) {
        return a.jm - b.jm;
    }

    return a.jd - b.jd;
};

export const jalaliKey = ({ jy, jm, jd }) => `${jy}-${String(jm).padStart(2, '0')}-${String(jd).padStart(2, '0')}`;

export const weekdayOfJalali = (jy, jm, jd) => {
    const { gy, gm, gd } = toGregorian(jy, jm, jd);
    const date = new Date(gy, gm - 1, gd);

    return (date.getDay() + 1) % 7;
};
