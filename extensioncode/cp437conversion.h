#ifndef PHP_iTEXTPHPCP437CONV_H
#define PHP_iTEXTPHPCP437CONV_H

#include "iTextPHP.h"
#include <wchar.h>

/*
wchar_t cp437table[] = {
     '\u00C7', '\u00FC', '\u00E9', '\u00E2', '\u00E4', '\u00E0', '\u00E5', '\u00E7', '\u00EA', '\u00EB', '\u00E8', '\u00EF', '\u00EE', '\u00EC', '\u00C4', '\u00C5',
            '\u00C9', '\u00E6', '\u00C6', '\u00F4', '\u00F6', '\u00F2', '\u00FB', '\u00F9', '\u00FF', '\u00D6', '\u00DC', '\u00A2', '\u00A3', '\u00A5', '\u20A7', '\u0192',
            '\u00E1', '\u00ED', '\u00F3', '\u00FA', '\u00F1', '\u00D1', '\u00AA', '\u00BA', '\u00BF', '\u2310', '\u00AC', '\u00BD', '\u00BC', '\u00A1', '\u00AB', '\u00BB',
            '\u2591', '\u2592', '\u2593', '\u2502', '\u2524', '\u2561', '\u2562', '\u2556', '\u2555', '\u2563', '\u2551', '\u2557', '\u255D', '\u255C', '\u255B', '\u2510',
            '\u2514', '\u2534', '\u252C', '\u251C', '\u2500', '\u253C', '\u255E', '\u255F', '\u255A', '\u2554', '\u2569', '\u2566', '\u2560', '\u2550', '\u256C', '\u2567',
            '\u2568', '\u2564', '\u2565', '\u2559', '\u2558', '\u2552', '\u2553', '\u256B', '\u256A', '\u2518', '\u250C', '\u2588', '\u2584', '\u258C', '\u2590', '\u2580',
            '\u03B1', '\u00DF', '\u0393', '\u03C0', '\u03A3', '\u03C3', '\u00B5', '\u03C4', '\u03A6', '\u0398', '\u03A9', '\u03B4', '\u221E', '\u03C6', '\u03B5', '\u2229',
            '\u2261', '\u00B1', '\u2265', '\u2264', '\u2320', '\u2321', '\u00F7', '\u2248', '\u00B0', '\u2219', '\u00B7', '\u221A', '\u207F', '\u00B2', '\u25A0', '\u00A0'
    
};
*/

wchar_t cp437table[] = { 0xC7, 0xFC, 0xE9, 0xE2, 0xE4, 0xE0, 0xE5, 0xE7, 0xEA, 0xEB, 0xE8, 0xEF, 0xEE, 0xEC, 0xC4, 0xC5, 0xC9, 0xE6, 0xC6, 0xF4, 0xF6, 0xF2, 0xFB, 0xF9, 0xFF, 0xD6, 0xDC, 0xA2, 0xA3, 0xA5, 0x20A7, 0x192, 0xE1, 0xED, 0xF3, 0xFA, 0xF1, 0xD1, 0xAA, 0xBA, 0xBF, 0x2310, 0xAC, 0xBD, 0xBC, 0xA1, 0xAB, 0xBB, 0x2591, 0x2592, 0x2593, 0x2502, 0x2524, 0x2561, 0x2562, 0x2556, 0x2555, 0x2563, 0x2551, 0x2557, 0x255D, 0x255C, 0x255B, 0x2510, 0x2514, 0x2534, 0x252C, 0x251C, 0x2500, 0x253C, 0x255E, 0x255F, 0x255A, 0x2554, 0x2569, 0x2566, 0x2560, 0x2550, 0x256C, 0x2567, 0x2568, 0x2564, 0x2565, 0x2559, 0x2558, 0x2552, 0x2553, 0x256B, 0x256A, 0x2518, 0x250C, 0x2588, 0x2584, 0x258C, 0x2590, 0x2580, 0x3B1, 0xDF, 0x393, 0x3C0, 0x3A3, 0x3C3, 0xB5, 0x3C4, 0x3A6, 0x398, 0x3A9, 0x3B4, 0x221E, 0x3C6, 0x3B5, 0x2229, 0x2261, 0xB1, 0x2265, 0x2264, 0x2320, 0x2321, 0xF7, 0x2248, 0xB0, 0x2219, 0xB7, 0x221A, 0x207F, 0xB2, 0x25A0, 0xA0 };


#endif
