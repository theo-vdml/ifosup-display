import { MaybeRef, toValue } from 'vue';

export interface DerivedTheme {
    primary: string;
    background: string;
    outline: string;
    foreground: string;
    accentBackground: string;
    accentForeground: string;
}

/**
 * Derives a theme based on a base hex color and whether the theme is dark or light.
 * @param isDark A ref or boolean indicating if the theme is dark.
 * @returns An object containing the derived theme colors.
 */
export function useThemeDerivation(isDark: MaybeRef<boolean> = false) {
    /**
     * RGB white and black constants for color mixing.
     */
    const white = { r: 255, g: 255, b: 255 };
    const black = { r: 0, g: 0, b: 0 };

    /**
     * Clamps a number to the 0-255 range and rounds it to the nearest integer.
     * @param value The number to clamp and round.
     * @returns The clamped and rounded number.
     */
    const clamp = (value: number) =>
        Math.max(0, Math.min(255, Math.round(value)));

    /**
     * Converts a hex color string to an RGB object.
     * @param hex The hex color string.
     * @returns An object with r, g, and b properties.
     */
    const hexToRgb = (hex: string) => {
        const clean = hex.replace('#', '');
        const normalized =
            clean.length === 3
                ? clean
                      .split('')
                      .map((c) => c + c)
                      .join('')
                : clean;
        const int = Number.parseInt(normalized, 16);

        return {
            r: (int >> 16) & 255,
            g: (int >> 8) & 255,
            b: int & 255,
        };
    };

    /**
     * Mixes two RGB colors by a given factor.
     * @param base The base RGB color.
     * @param target The target RGB color.
     * @param factor The mixing factor (0 to 1).
     * @returns The mixed RGB color.
     */
    const mixRgb = (
        base: { r: number; g: number; b: number },
        target: { r: number; g: number; b: number },
        factor: number,
    ) => {
        return {
            r: clamp(base.r + (target.r - base.r) * factor),
            g: clamp(base.g + (target.g - base.g) * factor),
            b: clamp(base.b + (target.b - base.b) * factor),
        };
    };

    /**
     * Converts an RGB object to a CSS rgb() string.
     * @param rgb The RGB color object.
     * @returns The CSS rgb() string.
     */
    const rgbToCss = (rgb: { r: number; g: number; b: number }) => {
        return `rgb(${rgb.r}, ${rgb.g}, ${rgb.b})`;
    };

    /**
     * Derives a theme based on a base hex color and whether the theme is dark or light.
     * @param baseHex The base hex color.
     * @returns The derived theme.
     */
    const deriveThemeFromBase = (baseHex: string): DerivedTheme => {
        const base = hexToRgb(baseHex);

        if (toValue(isDark)) {
            return {
                primary: rgbToCss(mixRgb(base, white, 0.1)),
                background: rgbToCss(mixRgb(base, black, 0.78)),
                outline: rgbToCss(mixRgb(base, black, 0.38)),
                foreground: rgbToCss(mixRgb(base, white, 0.72)),
                accentBackground: rgbToCss(mixRgb(base, black, 0.58)),
                accentForeground: rgbToCss(mixRgb(base, white, 0.64)),
            };
        }

        return {
            primary: rgbToCss(base),
            background: rgbToCss(mixRgb(base, white, 0.82)),
            outline: rgbToCss(mixRgb(base, black, 0.12)),
            foreground: rgbToCss(mixRgb(base, black, 0.55)),
            accentBackground: rgbToCss(mixRgb(base, white, 0.7)),
            accentForeground: rgbToCss(mixRgb(base, black, 0.4)),
        };
    };

    return {
        deriveThemeFromBase,
    };
}
