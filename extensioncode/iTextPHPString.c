#include "iTextPHPString.h"
#include "ext/iconv/php_iconv.h"

#ifdef HAVE_ICONV

#ifdef PHP_ICONV_H_PATH
#include PHP_ICONV_H_PATH
#else
#include <iconv.h>
#endif

#ifdef HAVE_GLIBC_ICONV
#include <gnu/libc-version.h>
#endif

#ifdef HAVE_LIBICONV
#undef iconv
#endif

#endif



/****** Some string utility functions *****/


int strrpos(char * haystack, char * needle)
{
    int needle_len, haystack_len;
    long offset = 0;
    char *p, *e;
    if (haystack == NULL && needle == NULL)
	return -1;
    needle_len = strlen(needle);
    haystack_len = strlen(haystack);
    
    if ((haystack_len == 0) || (needle_len == 0)) {
	return -1;
    }
    
    
    p = haystack;
    if (-offset > haystack_len) {
	e = haystack - needle_len;
    } else if (needle_len > -offset) {
	e = haystack + haystack_len - needle_len;
    } else {
	e = haystack + haystack_len + offset;
    }
    
    if (needle_len == 1) {
	/* Single character search can shortcut memcmps */
	while (e >= p) {
	    if (*e == *needle) {
		return e - p + (offset > 0 ? offset : 0);
	    }
	    e--;
	}
	return -1;
    }
    
    while (e >= p) {
	if (memcmp(e, needle, needle_len) == 0) {
	    return e - p + (offset > 0 ? offset : 0);
	}
	e--;
    }
   
    return -1;
}

int wstrrpos(wchar_t * haystack, wchar_t * needle)
{

    int needle_len, haystack_len;
    long offset = 0;
    wchar_t *p, *e;

    if (haystack == NULL && needle == NULL)
       return -1;

    needle_len = wcslen(needle);
    haystack_len = wcslen(haystack);
    if ((haystack_len == 0) || (needle_len == 0)) 
    {
       return -1;
    }

    p = haystack;
    if (-offset > haystack_len) 
    {
        e = haystack - needle_len;
    }
    else if (needle_len > -offset) 
    {
        e = haystack + haystack_len - needle_len;
    }
    else
    {
        e = haystack + haystack_len + offset;
    }

    if (needle_len == 1)
    {
        /* Single character search can shortcut memcmps */
        while (e >= p)
        {
            if (*e == *needle)
            {
                return e - p + (offset > 0 ? offset : 0);
            }
            e--;
        }
        return -1;
    }

    while (e >= p) 
    {
        if (wmemcmp (e, needle, needle_len) == 0)
        {
            return e - p + (offset > 0 ? offset : 0);
        }
        e--;
    }

    return -1;
}

/* {{{ proto resource itextphp_newString(string characterArray, int len, int type )
   create a new phpString object
   types: 1 = ansi, 2 = wchar, 3 = already wide   
*/

PHP_FUNCTION(itextphp_newString)
{
    int length, aType, Intrepofchar;
    char *character = NULL, *character2 = NULL;
    iconv_t cd;
    size_t result, out_left, out_leftOrig;
    unsigned char *ndl_buf = NULL, *ndl_bufStart = NULL;
    cd = iconv_open(UNICODEENCODING, DEFAULTENCODING);

    if (cd == (iconv_t)(-1))
    {
        RETURN_FALSE;
    }
    itextstring * theStringResource = (itextstring *)emalloc(sizeof(itextstring));
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"sl", &character, &length, &aType)!=FAILURE)
            {
                switch(aType)
                {
                    case STRINGANSI:
                    {
                        theStringResource->dataChar = (char *)emalloc(sizeof(char)*length+1);
                        strcpy(theStringResource->dataChar, character);
                        theStringResource->isWide = NOTTA;
                        theStringResource->length = length;
                        break;
                    }
                    case STRINGWIDE:
                    {
                        out_left = length*sizeof(char)*3;
                        out_leftOrig = out_left;
                        ndl_bufStart = ndl_buf = (char *)emalloc(out_left);
                        result = iconv(cd, (char **) &character, &length, (char **)&ndl_buf, &out_left);
                        ndl_bufStart[out_leftOrig-out_left] = '\0';
                        theStringResource->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*out_leftOrig+1);
                        theStringResource->length= sizeof(wchar_t)*out_leftOrig+1;
                        mbsrtowcs(theStringResource->dataWide, (const char **)&ndl_bufStart, out_leftOrig, 0);
                        break;
                    }
                    case ALREADYWIDE:
                    {
                        theStringResource->dataWide = (wchar_t *)pemalloc(sizeof(wchar_t)*length+1,1);
                        theStringResource->length= sizeof(wchar_t)*length+1;
                        mbsrtowcs(theStringResource->dataWide, (const char **)&character, length, 0);
                        break;
                    }
                    default:
                    {
                        RETURN_FALSE;
                        break;
                    }
                }
                ZEND_REGISTER_RESOURCE(return_value, theStringResource ,le_itextphpStringResource);
                return;
            }
            else if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3,"lll", &Intrepofchar, &length, &aType)!=FAILURE)
            {
                switch(aType)
                {
                    case STRINGANSI:
                    {
                        theStringResource->dataChar = (char *)emalloc(sizeof(char)*length+1);
                        theStringResource->dataChar[0] = (char)Intrepofchar;
                        theStringResource->isWide = NOTTA;
                        theStringResource->length = length;
                        break;
                    }
                    case STRINGWIDE:
                    {

                        out_left = length*sizeof(char) *3;
                        out_leftOrig = out_left;
                        ndl_bufStart = ndl_buf = (char *)emalloc(out_left);
                        unsigned char * from = (unsigned char*)emalloc(sizeof(char)*length);
                        from[0] = (char)Intrepofchar;
                        result = iconv(cd, (char **) &from, &length, (char **)&ndl_buf, &out_left);
                        ndl_bufStart[out_leftOrig-out_left] = '\0';
                        theStringResource->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);
                        theStringResource->length= sizeof(wchar_t)*length+1;
                        mbsrtowcs(theStringResource->dataWide, (const char **)&ndl_buf, length, 0);
                        break;
                    }
                    case ALREADYWIDE:
                    {
                        theStringResource->dataWide = (wchar_t *)pemalloc(sizeof(wchar_t)*length+1,1);
                        theStringResource->length=sizeof(wchar_t)*length+1;
                        unsigned char * value =(unsigned char*)emalloc(sizeof(char)*length);
                        value[0] = (char)Intrepofchar;
                        mbsrtowcs(theStringResource->dataWide, (const char **)&value, length, 0);
                        break;
                    }
                    default:
                    {
                        RETURN_FALSE;
                        break;
                    }
                }
                ZEND_REGISTER_RESOURCE(return_value, theStringResource ,le_itextphpStringResource);
            }
            break;
        }
        default:
        {
            RETURN_FALSE;
            break;
        }
    }
}



/* {{{ proto resource itextphp_newString(resource characterArray )
   returns string resource
*/

PHP_FUNCTION(itextphp_newStringfromChar)
{
    zval * aChar;
    char *thechar = NULL;

    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1,"r", &aChar)==FAILURE)
            {
                RETURN_NULL();
            }
            else
            {
                ZEND_FETCH_RESOURCE(thechar, char *, &aChar, -1, le_itextphpCharResourceName, le_itextphpCharResource);
                if (thechar != NULL)
                {
                   itextstring * theStringResource = (itextstring *)emalloc(sizeof(itextstring));
                   strcpy(theStringResource->dataChar, thechar);
                   theStringResource->isWide = NOTTA;
                   theStringResource->length = strlen(thechar);
                   //return resource
                   //now return the resource the user!
                   ZEND_REGISTER_RESOURCE(return_value, theStringResource ,le_itextphpStringResource);
                }
                else
                {
                    RETURN_NULL();
                }
            }
            break;
        }
        default:
            RETURN_NULL();
    }
}


/* {{{ proto int itextphp_StringLength(resource itextphp_String)
   returns length of the string  
*/

PHP_FUNCTION(itextphp_StringLength)
{
    zval * itextphp_String = NULL;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1,"r", &itextphp_String)==FAILURE)
            {
                RETURN_LONG(0);
            }
            else
	    {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString != NULL)
                {
                    RETURN_LONG(theString->length);
                }
            }
        }
    }
}

/* {{{ proto int itextphp_StringLength(resource itextphp_String, string needle)
   returns TRUE OR FALSE 
*/

PHP_FUNCTION(itextphp_string_startswith)
{
    zval * itextphp_String, * needle;
    iconv_t cd;
    size_t result, out_left;
    unsigned char *ndl_buf;
    itextstring *theString;
    char * newString = NULL;
    int length = 0;
    wchar_t * newchar = NULL;
    wchar_t *comparisonCharacterArray = NULL;
    switch(ZEND_NUM_ARGS())
    {

        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1,"rs", &itextphp_String, &needle)==FAILURE)
            {
                RETURN_FALSE
            }
            else
            {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString != NULL)
                {
                    if (theString->isWide == NOTTA)
                    {

                        newString = (char *)emalloc(sizeof(char)*Z_STRLEN_P(needle)+1);
                        strncpy(newString, Z_STRVAL_P(needle),Z_STRLEN_P(needle));
                        newString[Z_STRLEN_P(needle)] = '\0';
                        if (strcmp(newString, Z_STRVAL_P(needle))==0)
                        {
                            RETURN_TRUE
                        }
                        else
                        {
                            RETURN_FALSE
                        }

                    }
                    else
                    {
                        cd = iconv_open("UTF-16", "ISO-8859-1");
                        if (cd == (iconv_t)(-1))
                        {
                            RETURN_FALSE;
                        }

                        int length = Z_STRLEN_P(needle)*sizeof(int) + 15;
                        unsigned char * from = Z_STRVAL_P(needle);
                        result = iconv(cd, (char **) &from, &length, (char **)
                        &ndl_buf, &out_left);
                        newchar = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);
                        mbsrtowcs(newchar, (const char **)&ndl_buf, length, 0);
                        comparisonCharacterArray = (wchar_t *)emalloc(sizeof(wchar_t) * Z_STRLEN_P(needle)+1);
                        wcsncpy(comparisonCharacterArray, theString->dataWide, Z_STRLEN_P(needle));
                        comparisonCharacterArray[Z_STRLEN_P(needle)] = '\0';
                        if (wcscmp(comparisonCharacterArray, newchar) == 0)
                        {
                            RETURN_TRUE
                        }
                        else
                        {
                            RETURN_FALSE
                        }
                    }
                }
                else
                {
                    RETURN_FALSE
                }

            }
        }
    }
}



/* {{{ proto int itextphp_string_append(resource itextphp_String1, resource itextphp_String1)
   returns string resource
*/

PHP_FUNCTION(itextphp_string_append)
{
    zval * itextphp_String1, * itextphp_String2, * string;
    itextstring *theString1 = NULL;
    itextstring *theString2 = NULL;
    iconv_t cd;
    size_t result = 0, out_left = 0;
    int longValue = 0, length = 0;
    unsigned char *ndl_buf = NULL , * from = NULL;
    char * newString = NULL;
    wchar_t * theActualWideData = NULL, * newchar = NULL;
    char longValueStr = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rr", &itextphp_String1, &itextphp_String2)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(theString1, itextstring *, &itextphp_String1, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                ZEND_FETCH_RESOURCE(theString2, itextstring *, &itextphp_String2, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString1 != NULL && theString2 != NULL)
                {
                    if (theString1->isWide == NOTTA)
                    {
                        theString1->dataChar = (char *)erealloc(theString1->dataChar, sizeof(char)*theString1->length + theString2->length + 1);
                        strcat(theString1->dataChar, theString2->dataChar);
                        ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                    }
                    else
                    {
                        /**check to see if string2 is wide, if so convert the chars to wide...**/
                        if (theString2->isWide == NOTTA)
                        {
                            cd = iconv_open("UTF-16", "ISO-8859-1");
                            if (cd == (iconv_t)(-1))
                            {
                                RETURN_NULL();
                            }
                            length = strlen(theString2->dataChar)*sizeof(int) + 15;
                            from = theString2->dataChar;
                            result = iconv(cd, (char **) &from, &length, (char **) &ndl_buf, &out_left);
                            theActualWideData = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);			
                            mbsrtowcs(theActualWideData, (const char **)&ndl_buf, length, 0);
                        }
                        else
                        {
                            theActualWideData = theString2->dataWide;
                            length = theString2->length;
                        }
                        theString1->dataWide = (wchar_t *)erealloc(theString1->dataWide, sizeof(wchar_t)*theString1->length + length + 1);
                        wcscat(theString1->dataWide, theActualWideData);
                        ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
            else if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rs", &itextphp_String1, &string)!=FAILURE)
            {
                ZEND_FETCH_RESOURCE(theString1, itextstring *, &itextphp_String1, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString1 != NULL)
                {
                    newString = Z_STRVAL_P(string);
                    if (theString1->isWide == NOTTA)
                    {
                        if (newString == NULL)
                        {
                            RETURN_NULL();
                        }
                        else
                        {
                            theString1->dataChar = (char *)erealloc(theString1->dataChar, sizeof(char)*theString1->length + strlen(newString) + 1);
                            strcat(theString1->dataChar, newString);
                            ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                        }
                    }
                    else
                    {
                        theString1->dataWide = (wchar_t *)erealloc(theString1->dataWide, sizeof(wchar_t)*theString1->length + strlen(newString) + 1);
                        cd = iconv_open("UTF-16", "ISO-8859-1");
                        if (cd == (iconv_t)(-1))
                        {
                            RETURN_NULL();
                        }
                        length = Z_STRLEN_P(string)*sizeof(int) + 15;
                        from = newString;
                        result = iconv(cd, (char **) &from, &length, (char **) &ndl_buf, &out_left);
                        newchar = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);			
                        mbsrtowcs(newchar, (const char **)&ndl_buf, length, 0);
                        wcscat(theString1->dataWide, newchar);
                        ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                    }
                }
            }
            else if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, 2,"rl", &itextphp_String1, &string)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(theString1, itextstring *, &itextphp_String1, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString1 != NULL)
                {
                    longValue = Z_LVAL_P(string);
                    longValueStr = (char)longValue;
                    if (theString1->isWide == NOTTA)
                    {

                        theString1->dataChar = (char *)erealloc(theString1->dataChar, sizeof(char)*theString1->length + 1);
                        strcat(theString1->dataChar, &longValueStr);
                        ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                    }
                    else
                    {
                        theString1->dataWide = (wchar_t *)erealloc(theString1->dataWide, sizeof(wchar_t)*theString1->length +  1);
                        cd = iconv_open("UTF-16", "ISO-8859-1");
                        if (cd == (iconv_t)(-1))
                        {
                            RETURN_NULL();
                        }
                        length = sizeof(int) + 15;
                        from = (unsigned char *)emalloc(sizeof(unsigned char) + 1);
                        memcpy(from, &longValueStr,1);
                        result = iconv(cd, (char **) &from, &length, (char **) &ndl_buf, &out_left);
                        newchar = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);			
                        mbsrtowcs(newchar, (const char **)&ndl_buf, length, 0);
                        wcscat(theString1->dataWide, newchar);
                        ZEND_REGISTER_RESOURCE(return_value, theString1 ,le_itextphpStringResource);
                    }
                }
            }
            else if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"sr", &string, &itextphp_String1)!=FAILURE)
            {
                ZEND_FETCH_RESOURCE(theString1, itextstring *, &itextphp_String1, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString1 != NULL)
                {
                    theString2 = (itextstring *)emalloc(sizeof(itextstring));
                    newString = Z_STRVAL_P(string);
                    if (theString1->isWide == NOTTA)
                    {
                        if (newString == NULL)
                        {
                            RETURN_NULL();
                        }
                        else
                        {
                            theString2->dataChar = (char *)emalloc(sizeof(char)*theString1->length + strlen(newString) + 1);
                            strcpy(theString2->dataChar, theString1->dataChar);
                            strcat(theString2->dataChar, newString);
                            ZEND_REGISTER_RESOURCE(return_value, theString2,le_itextphpStringResource);
                        }
                    }
                    else
                    {
                        theString2->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*theString1->length + strlen(newString) + 1);
                        cd = iconv_open("UTF-16", "ISO-8859-1");
                        if (cd == (iconv_t)(-1))
                        {
                            RETURN_NULL();
                        }
                        length = Z_STRLEN_P(string)*sizeof(int) + 15;
                        from = newString;
                        result = iconv(cd, (char **) &from, &length, (char **) &ndl_buf, &out_left);
                        newchar = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);			
                        mbsrtowcs(newchar, (const char **)&ndl_buf, length, 0);
                        wcscpy(theString2->dataWide, theString1->dataWide);
                        wcscat(theString2->dataWide, newchar);
                        ZEND_REGISTER_RESOURCE(return_value, theString2,le_itextphpStringResource);
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
            else
            {
                RETURN_NULL();
            }
         }
     }
}


/* {{{ proto int itextphp_string_append(resource itextphp_String, int position)
   returns integer
*/

PHP_FUNCTION(itextphp_string_getIntFromIndex)
{
    zval * itextphp_String, * pos;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &itextphp_String, &pos)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString != NULL)
                {
                    if (theString->isWide == NOTTA)
                    {
                        RETURN_LONG((int)theString->dataChar[Z_LVAL_P(pos)]);
                    }
                    else
                    {
                        /**convert wchar_t to an int**/
                        RETURN_LONG((int)wctob(theString->dataWide[Z_LVAL_P(pos)]));
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
            break;
        }
    }
}



/* {{{ proto resource itextphp_string_substring(resource itextphp_String, int indexBegin, int indexEnd)
   returns resource string
*/

PHP_FUNCTION(itextphp_string_substring)
{
    zval * itextphp_String, * indexBegin, * indexEnd;
    itextstring *theString;
    itextstring *newString;
    int IndexBegin = 0, IndexEnd = 0, k = 0, substringSize = 0, place = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &itextphp_String, &indexBegin)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString != NULL)
                {
                    IndexBegin = Z_LVAL_P(indexBegin);
                    IndexEnd = theString->length;
                    substringSize = (IndexEnd) - IndexBegin;
                    if (theString->isWide == NOTTA)
                    {
                        newString = (itextstring *)emalloc(sizeof(itextstring));
                        newString->dataChar = (char *)emalloc(sizeof(char)*substringSize+1);
                        newString->isWide = NOTTA;
                        newString->length = substringSize;
                        for (k = IndexBegin; k < IndexEnd; k++)
                        {
                            newString->dataChar[place] = theString->dataChar[k];
                        }
                        newString->dataChar[newString->length] = '\0';
                    }
                    else
                    {
                        newString = (itextstring *)emalloc(sizeof(itextstring));
                        newString->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*substringSize+1);			
                        newString->length = substringSize;
                        for (k = IndexBegin; k < IndexEnd; k++)
                        {
                            newString->dataWide[place] = theString->dataWide[k];
                        }
                        newString->dataWide[newString->length] = L'\0';
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
            break;
        }
        case 3:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3,"rll", &itextphp_String, &indexBegin, &indexEnd)!=FAILURE)
            {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);

                if (theString != NULL)
               {
                    IndexBegin = Z_LVAL_P(indexBegin);
                    IndexEnd = Z_LVAL_P(indexEnd);
                    substringSize = (IndexEnd) - IndexBegin;
                    if (theString->isWide == NOTTA)
                    {
                        newString = (itextstring *)emalloc(sizeof(itextstring));
                        newString->dataChar = (char *)emalloc(sizeof(char)*substringSize+1);
                        newString->isWide = NOTTA;
                        newString->length = substringSize;
                        for (k = IndexBegin; k < IndexEnd; k++)
                        {
                            newString->dataChar[place] = theString->dataChar[k];
                        }
                        newString->dataChar[newString->length] = '\0';
                    }
                    else
                    {
                        newString = (itextstring *)emalloc(sizeof(itextstring));
                        newString->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*substringSize+1);			
                        newString->length = substringSize;
                        for (k = IndexBegin; k < IndexEnd; k++)
                        {
                            newString->dataWide[place] = theString->dataWide[k];
                        }
                        newString->dataWide[newString->length] = L'\0';
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
            break;
        }
    }
}

/* {{{ proto int itextphp_string_lastIndexOf(resource itextphp_String_haystack, resource itextphp_string_needle)
   returns int
*/

PHP_FUNCTION(itextphp_string_lastIndexOf)
{
    zval * itextphp_String_haystack, * itextphp_string_needle;
    itextstring *Itextphp_String_haystack = NULL, *Itextphp_string_needle = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
	    if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rr", &itextphp_String_haystack, &itextphp_string_needle)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(Itextphp_String_haystack, itextstring *, &itextphp_String_haystack, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                ZEND_FETCH_RESOURCE(Itextphp_string_needle, itextstring *, &itextphp_string_needle, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (Itextphp_String_haystack != NULL && Itextphp_string_needle != NULL)
                {
                    if (Itextphp_String_haystack->isWide == NOTTA)
                    {
                        RETURN_LONG(strrpos(Itextphp_String_haystack->dataChar, Itextphp_string_needle->dataChar));
                    }
                    else
                    {
                        RETURN_LONG(wstrrpos(Itextphp_String_haystack->dataWide, Itextphp_string_needle->dataWide));
                    }
                }
                else
                {
                    RETURN_LONG(-1);
                }
            }
        }
    }
}

/* {{{ proto boolean itextphp_string_equals(resource itextphp_String1, resource itextphp_String2)
   returns boolean
*/

PHP_FUNCTION(itextphp_string_equals)
{
    zval * itextphp_String1, * itextphp_String2;
    itextstring *Itextphp_String1 = NULL, *Itextphp_String2 = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rr", &itextphp_String1, &itextphp_String2)!=FAILURE)
            {
                ZEND_FETCH_RESOURCE(Itextphp_String1, itextstring *, &itextphp_String1, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                ZEND_FETCH_RESOURCE(Itextphp_String2, itextstring *, &itextphp_String2, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (Itextphp_String1 != NULL && Itextphp_String2 != NULL)
                {
                    if (Itextphp_String1->isWide == NOTTA && Itextphp_String2->isWide == NOTTA)
                    {
                        if (strcmp(Itextphp_String1->dataChar, Itextphp_String2->dataChar) == 0)
                        {
                            RETURN_TRUE
                        }
                        else
                        {
                            RETURN_FALSE
                        }
                    }
                    else if (Itextphp_String1->isWide != NOTTA && Itextphp_String2->isWide != NOTTA)
                    {
                        if (wcscmp(Itextphp_String1->dataWide, Itextphp_String2->dataWide) == 0)
                        {
                            RETURN_TRUE;
                        }
                        else
                        {
                            RETURN_FALSE;
                        }
                    }
                }
                else
                {
                    RETURN_FALSE
                }
            }
        }
    }
}


/* {{{ proto string itextphp_string_toPHPString(resource itextphp_String)
   returns string
*/

PHP_FUNCTION(itextphp_string_toPHPString)
{
    zval * itextphp_String;
    itextstring *Itextphp_String = NULL;
    char * theNewString = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1,"r", &itextphp_String)!=FAILURE)
	    {
                ZEND_FETCH_RESOURCE(Itextphp_String, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (Itextphp_String != NULL)
                {
                    if (Itextphp_String->isWide == NOTTA)
                    {
                        RETURN_STRING(Itextphp_String->dataChar,1);
                    }
                    else
                    {
                        theNewString = (char*)emalloc(sizeof(char) * Itextphp_String->length +1);
                        wcstombs(theNewString, Itextphp_String->dataWide, Itextphp_String->length+1);
                        RETURN_STRING(theNewString, 1);
                    }
                }
                else
                {
                    RETURN_NULL();
                }
            }
        }
    }
}


/* {{{ proto int itextphp_string_toInteger(resource itextphp_String)
   returns int
*/

PHP_FUNCTION(itextphp_string_toInteger)
{
    zval * itextphp_String;
    itextstring *Itextphp_String = NULL;
    wchar_t *endptr = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1,"r", &itextphp_String)!=FAILURE)
            {
                ZEND_FETCH_RESOURCE(Itextphp_String, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (Itextphp_String != NULL)
                {
                    if (Itextphp_String->isWide == NOTTA)
                    {
                        if (Itextphp_String->dataChar != NULL)
                        {
                            RETURN_LONG(atoi(Itextphp_String->dataChar))
                        }
                        else
                        {
                            RETURN_LONG(-1)
                        }
                    }
                    else
                    {
                        if (Itextphp_String->dataWide != NULL)
                        {
                            RETURN_LONG(wcstol(Itextphp_String->dataWide, &endptr, 0))
                        }
                        else
                        {
                            RETURN_LONG(-1)
                        }
                    }
                }
                else
                {
                    RETURN_LONG(-1)
                }
            }
        }
    }
}







