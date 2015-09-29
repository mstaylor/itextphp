#include "itextPHP.h"
#include "itextphpString.h"

/* {{{ proto itextphp_char_getInt(resource itextphp_String, int place)
   returns a char resource  
*/

PHP_FUNCTION(itextphp_char_getInt)
{
    zval * itextphp_String, * place;
    int actPlace = 0;
    
    itextstring *theString;
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rl", &itextphp_String, &place)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(theString, itextstring *, &itextphp_String, -1, le_itextphpStringResourceName, le_itextphpStringResource);
		if (theString != NULL)
		{
		    char char1;
		    convert_to_long(place);
		    actPlace = Z_LVAL_P(place);
		    if (theString->dataChar != NULL)
		    {
		        char1 = theString->dataChar[actPlace];
		    }
		    else if (theString->dataWide != NULL)
		    {
			char1 = wctob(theString->dataWide[actPlace]);
		    }
		    
		    //now return the char resource
		    ZEND_REGISTER_RESOURCE(return_value, &char1 ,le_itextphpCharResource);
		}
	    }
	    break;
	}
    }
}


/* {{{ proto itextphp_char_getIntRep(resource aChar, int place)
   returns a int representation of the char  
*/

PHP_FUNCTION(itextphp_char_getIntRep)
{
    zval * aChar, * place, * bitwise1;
    int actPlace = 0;
    int Bitwise1 = 0;
    
    char *thechar = NULL;
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rl", &aChar, &place)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(thechar, char *, &aChar, -1, le_itextphpCharResourceName, le_itextphpCharResource);
		if (thechar != NULL)
		{
		    
		    convert_to_long(place);
		    actPlace = Z_LVAL_P(place);
		        
		    RETURN_LONG((int)thechar[actPlace]);
		}
	    }
	    break;
	}
	case 3:
	{
	    if(zend_parse_parameters(3,"rll", &aChar, &place, &bitwise1)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(thechar, char *, &aChar, -1, le_itextphpCharResourceName, le_itextphpCharResource);
		if (thechar != NULL)
		{
		    
		    convert_to_long(place);
		    actPlace = Z_LVAL_P(place);
		    convert_to_long(bitwise1);
		    Bitwise1 = Z_LVAL_P(bitwise1);
		        
		    RETURN_LONG((int)thechar[actPlace] & Bitwise1);
		}
	    }
	    break;
	}
    }
}



/* {{{ proto itextphp_char_create(string value)
   returns a char resource  
*/

PHP_FUNCTION(itextphp_char_create)
{
    zval * value;
    char * theValue;
    
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 1:
	{
	    if(zend_parse_parameters(1,"s", &value)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		convert_to_string(value);
		theValue = Z_STRVAL_P(value);
		if (theValue != NULL)
		{
		    char theResource = theValue[0];
		    //register and return the char resource
		    ZEND_REGISTER_RESOURCE(return_value, &theResource ,le_itextphpCharResource);
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



