#include "itextPHP.h"


/* {{{ proto int itextphp_bytes_getSize(array newline, int index)
   return the size of a byte resource */
PHP_FUNCTION(itextphp_multiArrayByteDataTypeSize)
{
    zval * newline, * index;
    int actIndex = 0;

    byte * aByte = NULL;
    
    switch(ZEND_NUM_ARGS())
    {
	
        case 2:
	{
	    if(zend_parse_parameters(2,"al", &newline, &index)==FAILURE)
	    {
	        RETURN_LONG(-1);
	    }
	    else
	    {
		convert_to_long(index);
		actIndex = Z_LVAL_P(index);
		zval *rawByte;
		if (zend_hash_index_find(Z_ARRVAL_P(newline), actIndex, (void**)&rawByte) != SUCCESS)
		{
		    RETURN_LONG(-1)
		}
		//get the byte resource
	        ZEND_FETCH_RESOURCE(aByte, byte *, &rawByte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte == NULL)
		{
		    RETURN_LONG(-1);
		}
		else
		{
		    RETURN_LONG(aByte->size);
		}
		
		
	    }
	}
	default:
	    RETURN_LONG(-1);
      }
}

/* {{{ proto int itextphp_multiArrayByteDataTypeValue(array newline, int index)
   return the byte resource */
PHP_FUNCTION(itextphp_multiArrayByteDataTypeValue)
{
    zval * newline, * index;
    int actIndex = 0;
    byte * aByte = NULL;
    
    switch(ZEND_NUM_ARGS())
    {
	
        case 2:
	{
	    if(zend_parse_parameters(2,"al", &newline, &index)==FAILURE)
	    {
		RETURN_NULL();
	    }
	    else
	    {
		convert_to_long(index);
		actIndex = Z_LVAL_P(index);
		
		if (zend_hash_index_find(Z_ARRVAL_P(newline), actIndex, (void**)&return_value) != SUCCESS)
		{
		    RETURN_NULL();
		}
		else
		{
		    RETURN_RESOURCE(return_value); 
		}
	    }
	}
	default:
	    RETURN_NULL();
    }
    
}






