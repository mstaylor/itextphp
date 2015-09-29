#ifndef PHP_iTEXTPHPSTRING_H
#define PHP_iTEXTPHPSTRING_H

#include "iTextPHP.h"
#include <wchar.h>

#define UNICODEENCODING "UTF-8"
#define DEFAULTENCODING "ISO-8859-1"

typedef struct itextstring
{

    char * dataChar;//the characters if ansi
    wchar_t * dataWide;//the characters if wide
    int isWide;//is this a wide character
    int length;//the length of the string
} itextstring;


#endif
