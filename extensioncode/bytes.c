#include "itextPHP.h"
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


/* {{{ proto int itextphp_bytes_getSize(resource bytearray)
   return the size of a byte resource */
PHP_FUNCTION(itextphp_bytes_getSize)
{
    zval * bytearray;
    byte * aByte;
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"r", &bytearray)==FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &bytearray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte== NULL)
		    RETURN_LONG(0)
		else
		    RETURN_LONG(aByte->size);
	    }
	    break;
	}
	default:
             RETURN_LONG(0); 
    }
}

/* {{{ proto  itextphp_bytes_create(int length)
   return a byte resource of size length */

PHP_FUNCTION(itextphp_bytes_create)
{
    
    zval * length, *  theArray;
    int actLength = 0;
    int k =0;
    byte * aByte;
    
    switch(ZEND_NUM_ARGS())
    {
	case 1:
	{
	    if (zend_parse_parameters(1, "l", &length) != FAILURE)
            {
	        convert_to_long(length);
		actLength = Z_LVAL_P(length);
		aByte = (byte*) emalloc(sizeof(byte));
		aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*actLength);
		aByte->size = actLength;
		aByte->pointer = 0;
		ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
	    }
	    else if (zend_parse_parameters(1, "a", &theArray) != FAILURE)
	    {
  	        HashPosition pos;	
		convert_to_array_ex(&theArray);
		int size = zend_hash_num_elements(HASH_OF(theArray));
		
		aByte = (byte*) emalloc(sizeof(byte));
		aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*size);
		aByte->size = size;
		aByte->pointer = 0;
		
		
		
		zend_hash_internal_pointer_reset_ex(HASH_OF(theArray), &pos);
		zval ** item;
		int count = 0;
                for (k = 0;; zend_hash_move_forward_ex(HASH_OF(theArray), &pos))	{
		    if (zend_hash_get_current_data_ex(HASH_OF(theArray), (void **) &item, &pos) == FAILURE)
		    {
			break;
		    }
		    convert_to_long_ex(item);
		    int theItem = Z_LVAL_PP(item);
		    aByte->theByte[count] = theItem;
		    
	            count++;	    
		}
	    }
	    break;
	}
    }
}


/* {{{ proto  itextphp_bytes_createfromRaw(string rawdata)
   return a byte resource based on rawdata */

PHP_FUNCTION(itextphp_bytes_createfromRaw)
{
    
    zval * rawdata;
    char * actRawData;
    byte * aByte;
    
    switch(ZEND_NUM_ARGS())
    {
	case 1:
	{
	    if (zend_parse_parameters(1, "s", &rawdata) != FAILURE)
            {
	        convert_to_string(rawdata);
		actRawData = Z_STRVAL_P(rawdata);
		aByte = (byte*) emalloc(sizeof(byte));
		aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*Z_STRLEN_P(rawdata));
		aByte->size = Z_STRLEN_P(rawdata);
		aByte->pointer = 0;
		//copy the data
		memcpy(aByte->theByte, actRawData,Z_STRLEN_P(rawdata));
		ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
	    }
	    break;
	}
    }
}

/* {{{ proto  itextphp_bytes_createfromInt(int data)
   return a byte resource based on int */

PHP_FUNCTION(itextphp_bytes_createfromInt)
{
    
    zval * data;
    int actData;
    byte * aByte;
    
    switch(ZEND_NUM_ARGS())
    {
	case 1:
	{
	    if (zend_parse_parameters(1, "l", &data) != FAILURE)
            {
	        convert_to_long(data);
		actData = Z_LVAL_P(data);
		aByte = (byte*) emalloc(sizeof(byte));
		aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char));
		aByte->size = 1;
		//copy the data
		aByte->theByte[0] = (unsigned char)actData;
		ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
	    }
	    break;
	}
    }
}



/* {{{ proto itextphp_bytes_update(resource aByte, int position, int value)
   return updates the byte resource */

PHP_FUNCTION(itextphp_bytes_update)
{
    zval *abyte, * position, * value;
    byte * aByte;
    int actPosition = 0;
    int actValue = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rll", &abyte, &position, &value) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    actPosition = Z_LVAL_P(position);
		    convert_to_long(value);
		    actValue = Z_LVAL_P(value);
		    aByte->theByte[actPosition] = (unsigned char)actValue;
		}
	    }
	    break;
	}
    }
}



/* {{{ proto itextphp_bytes_write(resource destByte, resource sourceByte)
  updates the dest byte resource */

PHP_FUNCTION(itextphp_bytes_write)
{
    zval *destByte, * sourceByte, * intValue;
    zval *abyte, * position, * bbyte, * position2;
    zval *strValue;
    char * StrValue = NULL;
    byte * aByte, * bByte;
    int k = 0;
    int IntValue = 0;
    int aPosition = 0;
    int bPosition = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 2:
	{
	    if (zend_parse_parameters(2, "rr", &destByte, &sourceByte ) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &destByte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(bByte, byte *, &sourceByte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL && bByte != NULL)
		{
		   if (aByte->pointer+bByte->size > aByte->size)
		   {
			///need to reallocate to block to avoid buffer overload
			aByte->theByte = (unsigned char*)erealloc(aByte->theByte, (sizeof(unsigned char)*aByte->size) + ((aByte->pointer+bByte->size) - aByte->size));
			aByte->size = aByte->size + ((aByte->pointer+bByte->size) - aByte->size);
		   }
		   for (k=0; k < bByte->size; k++)
		   {
			aByte->theByte[aByte->pointer] = bByte->theByte[k];
			aByte->pointer++;
		   }
		}
	    }
	    else if (zend_parse_parameters(2, "rl", &destByte, &intValue ) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &destByte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!=NULL)
		{
		    convert_to_long(intValue);
		    IntValue = Z_LVAL_P(intValue);
		    aByte->theByte[aByte->pointer] = (unsigned char)IntValue;
		    aByte->pointer++;
		}
	    }
	    else if (zend_parse_parameters(2, "rs", &destByte, &strValue) != FAILURE)
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &destByte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		StrValue = Z_STRVAL_P(strValue);
		if (aByte!=NULL && StrValue != NULL)
		{
		    for (k=0; k < strlen(StrValue); k++)
		    {
			aByte->theByte[aByte->pointer] = StrValue[k];
			aByte->pointer++;
		    }
		}
	    }
	    break;
	}
	case 4:
	{
	    if (zend_parse_parameters(4, "rlrl", &abyte, &position, &bbyte, & position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		ZEND_FETCH_RESOURCE(bByte, byte *, &bbyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL || bByte != NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    aByte->theByte[aPosition] =  bByte->theByte[bPosition];
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	}
    }
}


/* {{{ proto itextphp_bytes_bitwiseAssign(resource destByte,  int destpos, resource sourceByte, int srcpos)
  updates the dest byte resource */
PHP_FUNCTION(itextphp_bytes_bitwiseAssign)
{
    
    zval *abyte, * position, * bbyte, * position2;
    byte * aByte, * bByte; 
    int aPosition = 0;
    int bPosition = 0;
    
    switch(ZEND_NUM_ARGS())
    {
	case 3:
	{
	    if (zend_parse_parameters(3, "rll", &abyte, &position, &position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    aByte->theByte[aPosition] |=  bPosition;
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	}
        case 4:
	{
	    if (zend_parse_parameters(4, "rlrl", &abyte, &position, &bbyte, & position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		ZEND_FETCH_RESOURCE(bByte, byte *, &bbyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL || bByte != NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    aByte->theByte[aPosition] |=  bByte->theByte[bPosition];
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	}
	
    }
}




/* {{{ proto itextphp_updateByteWithByte(resource aByte, int position, source bByte, int position)
    */

PHP_FUNCTION(itextphp_updateByteWithByte)
{
    zval *abyte, * position, * bbyte, * position2, * divisor, *bitwise1, *bitwise2;
    byte * aByte;
    byte * bByte;
    int aPosition = 0;
    int bPosition = 0;
    int aDivisor = 0;
    int aBitwise1, aBitwise2;
    switch(ZEND_NUM_ARGS())
    {
	
	case 4:
	{
	    if (zend_parse_parameters(4, "rlrl", &abyte, &position, &bbyte, & position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		ZEND_FETCH_RESOURCE(bByte, byte *, &bbyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL || bByte != NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    aByte->theByte[aPosition] =  aByte->theByte[aPosition] + bByte->theByte[bPosition];
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	}
	case 5:
	{
	    if (zend_parse_parameters(5, "rlrll", &abyte, &position, &bbyte, &position2, &divisor) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		ZEND_FETCH_RESOURCE(bByte, byte *, &bbyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL || bByte != NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    convert_to_long(divisor);
		    aDivisor = Z_LVAL_P(divisor);
		    
		    
		    
		    aByte->theByte[aPosition] =  aByte->theByte[aPosition]  + (bByte->theByte[bPosition])/aDivisor;
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	}
	case 7:
	{
	      if (zend_parse_parameters(7, "rlrllll", &abyte, &position, &bbyte, &position2, &divisor, &bitwise1, &bitwise2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		ZEND_FETCH_RESOURCE(bByte, byte *, &bbyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL || bByte != NULL)
		{
		    convert_to_long(position);
		    aPosition = Z_LVAL_P(position);
		    
		    convert_to_long(position2);
		    bPosition = Z_LVAL_P(position2);
		    
		    convert_to_long(divisor);
		    aDivisor = Z_LVAL_P(divisor);
		    
		     convert_to_long(bitwise1);
		    aBitwise1 = Z_LVAL_P(bitwise1);
		     convert_to_long(bitwise2);
		    aBitwise2 = Z_LVAL_P(bitwise2);
		    
		    aByte->theByte[aPosition] =  aByte->theByte[aPosition] & aBitwise1 + (bByte->theByte[bPosition] & aBitwise2)/aDivisor;
		    
		    //aByte->theByte[actPosition] = (unsigned char)actValue;
		}
		
	    }
	    break;
	    
	}
    }
}





/* {{{ proto itextphp_bytes_getIntValue(resource aByte, int position, int bitwise)
   return int associated with the index */

PHP_FUNCTION(itextphp_bytes_getIntValue)
{
    zval *abyte, * position, * value;
    byte * aByte;
    int actPosition = 0;
    int actValue = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rll", &abyte, &position, &value) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    actPosition = Z_LVAL_P(position);
		    convert_to_long(value);
		    actValue = Z_LVAL_P(value);
		    
		    RETURN_LONG((int)(actValue & aByte->theByte[actPosition]));

		}
	    }
	    
	    break;
	}
	case 2:
	{
	    if (zend_parse_parameters(3, "rl", &abyte, &position) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    actPosition = Z_LVAL_P(position);
		    
		    
		    RETURN_LONG((int)(aByte->theByte[actPosition]));

		}
	    }
	    break;
	}
    }
}



/* {{{ proto itextphp_bytes_lessthanoperator(resource aByte, resource aByte, int byte1 location, int byte2 location)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_lessthanoperator)
{
    zval *abyte1, * abyte2, * position1, * position2;
    byte * aByte1, * aByte2;
    int actPosition1, actPosition2;
    
    switch(ZEND_NUM_ARGS())
    {
	
	case 4:
	{
	    if (zend_parse_parameters(4, "rrll", &abyte1, &abyte2, &position1, &position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL && aByte2 != NULL)
		{
		    convert_to_long(position1);
		    actPosition1 = Z_LVAL_P(position1);
		    convert_to_long(position2);
		    actPosition2 = Z_LVAL_P(position2);
		    
		    if (aByte1->theByte[actPosition1] < aByte2->theByte[actPosition2])
			RETURN_TRUE
		    else
			RETURN_FALSE

		}
		else
		{
		    RETURN_FALSE;
		}
	    }
	    break;
	}
    }
}

/* {{{ proto itextphp_bytes_greaterthanoperator(resource aByte, resource aByte, int byte1 location, int byte2 location)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_greaterthanoperator)
{
    zval *abyte1, * abyte2, * position1, * position2;
    byte * aByte1, * aByte2;
    int actPosition1, actPosition2;
    
    switch(ZEND_NUM_ARGS())
    {
	
	case 4:
	{
	    if (zend_parse_parameters(4, "rrll", &abyte1, &abyte2, &position1, &position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL && aByte2 != NULL)
		{
		    convert_to_long(position1);
		    actPosition1 = Z_LVAL_P(position1);
		    convert_to_long(position2);
		    actPosition2 = Z_LVAL_P(position2);
		    
		    if (aByte1->theByte[actPosition1] > aByte2->theByte[actPosition2])
			RETURN_TRUE
		    else
			RETURN_FALSE

		}
		else
		{
		    RETURN_FALSE
		}
	    }
	    break;
	}
    }
}





/* {{{ proto itextphp_bytes_equalsoperator(resource aByte, resource aByte, int byte1 location, int byte2 location)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_equalsoperator)
{
    zval *abyte1, * abyte2, * position1, * position2, * position3;
    byte * aByte1, * aByte2;
    int actPosition1, actPosition2, actPosition3;
    
    switch(ZEND_NUM_ARGS())
    {
    
        
            
	case 4:
	{
	    if (zend_parse_parameters(4, "rrll", &abyte1, &abyte2, &position1, &position2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL && aByte2 != NULL)
		{
		    convert_to_long(position1);
		    actPosition1 = Z_LVAL_P(position1);
		    convert_to_long(position2);
		    actPosition2 = Z_LVAL_P(position2);
		    
		    if (aByte1->theByte[actPosition1] == aByte2->theByte[actPosition2])
			RETURN_TRUE
		    else
			RETURN_FALSE

		}
		else
		{
		    RETURN_FALSE
		}
	    }
	    else if (zend_parse_parameters(4, "rlll", &abyte1, &position1, &position2, &position3) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL)
		{
		    convert_to_long(position1);
		    actPosition1 = Z_LVAL_P(position1);
		    convert_to_long(position2);
		    actPosition2 = Z_LVAL_P(position2);
		    convert_to_long(position3);
		    actPosition3 = Z_LVAL_P(position3);
		    if (aByte1->theByte[actPosition1] & actPosition2 == actPosition3)
		    {
			RETURN_TRUE
		    }
		    else
		    {
			RETURN_FALSE
		    }
		}
	    }
	    break;
	}
    }
}




/* {{{ proto itextphp_bytes_equalsoperator(resource aByte, int location1, int location2, int location3, int location4)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_notequaloperator)
{  
    zval *abyte1, * position1, * position2, * position3;
    byte * aByte1;
    int actPosition1, actPosition2, actPosition3;
    
    switch(ZEND_NUM_ARGS())
    {
    
        
            
	case 4:
	{
	    if (zend_parse_parameters(4, "rlll", &abyte1, &position1, &position2, &position3) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL)
		{
		    convert_to_long(position1);
		    actPosition1 = Z_LVAL_P(position1);
		    convert_to_long(position2);
		    actPosition2 = Z_LVAL_P(position2);
		    convert_to_long(position3);
		    actPosition3 = Z_LVAL_P(position3);
		    if (aByte1->theByte[actPosition1] & actPosition2 != actPosition3)
		    {
			RETURN_TRUE
		    }
		    else
		    {
			RETURN_FALSE
		    }
		}
	    }
	    break;
	}
    }	
}




/* {{{ proto itextphp_bytes_equalsoperatorObject(resource aByte, resource aByte)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_equalsoperatorObject)
{
    zval *abyte1, * abyte2;
    byte * aByte1, * aByte2;
    int actPosition1, actPosition2;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 2:
	{
	    if (zend_parse_parameters(2, "rr", &abyte1, &abyte2) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte1!= NULL && aByte2 != NULL)
		{
		    if (aByte1->size != aByte2->size)
			RETURN_FALSE
		    else
		    {
		        for (k = 0; k < aByte1->size; k++)
			{
			    if (aByte1->theByte[k] != aByte2->theByte[k])
			    {
			        RETURN_FALSE
		            }
		        }
			RETURN_TRUE
		    }
		    

		}
		else
		{
		    RETURN_FALSE
		}
	    }
	    break;
	}
    }
}

/* {{{ proto itextphp_bytes_equalsAnotherChar(resource aByte, int location, string character)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_equalsAnotherChar)
{
    zval *abyte1, * location, * character;
    byte * aByte1;
    int actPosition1, actCharacter;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rls", &abyte1, &location, &character) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		convert_to_long(location);
		actPosition1 = Z_LVAL_P(location);
		
		convert_to_long(character);
		actCharacter = Z_LVAL_P(character);
		
		if ((int)aByte1->theByte[actPosition1] == actCharacter)
		    RETURN_TRUE
		else
		    RETURN_FALSE
		
	    }
	    else
	    {
		RETURN_FALSE
	    }
	}
    }
}


/* {{{ proto itextphp_bytes_greaterthanequalAnotherChar(resource aByte, int location, string character)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_greaterthanequalAnotherChar)
{
    zval *abyte1, * location, * character;
    byte * aByte1;
    int actPosition1, actCharacter;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rls", &abyte1, &location, &character) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		convert_to_long(location);
		actPosition1 = Z_LVAL_P(location);
		
		convert_to_long(character);
		actCharacter = Z_LVAL_P(character);
		
		if ((int)aByte1->theByte[actPosition1] >= actCharacter)
		    RETURN_TRUE
		else
		    RETURN_FALSE
		
	    }
	    else
	    {
		RETURN_FALSE
	    }
	}
    }
}



/* {{{ proto itextphp_bytes_lessthanequalAnotherChar(resource aByte, int location, string character)
   returns TRUE or FALSE */

PHP_FUNCTION(itextphp_bytes_lessthanequalAnotherChar)
{
    zval *abyte1, * location, * character;
    byte * aByte1;
    int actPosition1, actCharacter;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rls", &abyte1, &location, &character) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		convert_to_long(location);
		actPosition1 = Z_LVAL_P(location);
		
		convert_to_long(character);
		actCharacter = Z_LVAL_P(character);
		
		if ((int)aByte1->theByte[actPosition1] <= actCharacter)
		    RETURN_TRUE
		else
		    RETURN_FALSE
		
	    }
	    else
	    {
		RETURN_FALSE
	    }
	}
    }
}

/* {{{ proto itextphp_bytes_readsequence(resource aByte, int start, int count)
   returns byte resource */

PHP_FUNCTION(itextphp_bytes_readsequence)
{
    zval *abyte1, * location, * count;
    byte * aByte1;
    byte * newByte;
    int actLocation, actCount;
    int k = 0;
    int offset = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rll", &abyte1, &location, &count) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte1, byte *, &abyte1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		convert_to_long(location);
		actLocation = Z_LVAL_P(location);
		
		convert_to_long(count);
		actCount = Z_LVAL_P(count);
		
		newByte = (byte*) emalloc(sizeof(byte));
		newByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*actCount);
		newByte->size = actCount;
		
		offset = k + actCount;
		for (k = actLocation; k < offset; k++)
		{
		    newByte->theByte[k] = aByte1->theByte[k];
		}
		ZEND_REGISTER_RESOURCE(return_value, newByte ,le_itextphpanotherByteResource);
		
	    }
	    else
	    {
		RETURN_NULL()
	    }
	}
    }
}


/* {{{ proto itextphp_bytes_readsequence(resource aByte, int start, int count)
   returns byte resource */

PHP_FUNCTION(itextphp_write_bytes_to_page)
{
    zval * abyte, * position;
    byte * aByte;
   
    
    switch(ZEND_NUM_ARGS())
    {
	
	case 2:
	{
	    if (zend_parse_parameters(2, "rl", &abyte, &position) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte != NULL)
		{
		    if (aByte->theByte != NULL)
		    {
			unsigned char * buf = (unsigned char*)emalloc(sizeof(unsigned char));
			buf[0] = aByte->theByte[Z_LVAL_P(position)];
			
			zend_printf(buf);
		    }
		}
	    }
	}
    }
    
    
}

/* {{{ proto resource itextphp_getAnsiString(resource bytearray, int len)
   convert a byte to a php string */
PHP_FUNCTION(itextphp_getAnsiString)
{
    
    /* the original itext java blurb */
    
     /*   char c[] = new char[bytes.length];
            for (int k = 0; k < bytes.length; ++k)
                c[k] = (char)(bytes[k] & 0xff);
            return new String(c);*/
    zval * byteArray, * len;
    int length = 0;
    byte * theByte;
    int k = 0;
    
   
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rl", &byteArray, &len)==FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		convert_to_long(len);
		length = Z_LVAL_P(len);
		
		ZEND_FETCH_RESOURCE(theByte, byte *, &byteArray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		char * dataChar = (char *)emalloc(sizeof(char)*length+1);
		
		for (k = 0; k < theByte->size; k ++)
		{
		    dataChar[k] = (char)(theByte->theByte[k]);    
		}
		
		dataChar[length] = '\0';
		
		RETURN_STRING(dataChar, 1);
		
		
	    }
	}
    }
    
    
}

/* {{{ proto itextphp_bytebuffer_appendhex(resource aByte, int position, int bitwise)
   return int associated with the index */

PHP_FUNCTION(itextphp_bytebuffer_appendhex)
{
    zval *abyte, * position, * value;
    byte * aByte;
    int actPosition = 0;
    int actValue = 0;
    switch(ZEND_NUM_ARGS())
    {
	
	case 3:
	{
	    if (zend_parse_parameters(3, "rll", &abyte, &position, &value) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    actPosition = Z_LVAL_P(position);
		    convert_to_long(value);
		    actValue = Z_LVAL_P(value);
		    
		    RETURN_LONG((int)(aByte->theByte[actPosition]>>4) && actValue);

		}
	    }
	    
	    
	    break;
	}
	case 4:
	{
	    if (zend_parse_parameters(4, "rlll", &abyte, &position, &value) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		    convert_to_long(position);
		    actPosition = Z_LVAL_P(position);
		    convert_to_long(value);
		    actValue = Z_LVAL_P(value);
		    
		    RETURN_LONG((int)(aByte->theByte[actPosition]) && actValue);

		}
	    }
	    break;
	}
	
    }
}


/* {{{ proto itextphp_bytes_getMD5Digest(resource aByte)
   return byte resource */

PHP_FUNCTION(itextphp_bytes_getMD5Digest)
{
    zval *abyte;
    byte * aByte, * bByte;
    switch(ZEND_NUM_ARGS())
    {
	
	case 1:
	{
	    if (zend_parse_parameters(1, "r", &abyte) != FAILURE)
            {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		if (aByte!= NULL)
		{
		   PHP_MD5_CTX   md5ctx;
		   unsigned char hash[16];
                   PHP_MD5Init(&md5ctx);
		   PHP_MD5Update(&md5ctx, aByte->theByte, aByte->size);
		   PHP_MD5Final(hash, &md5ctx);
		   make_digest(aByte->theByte, hash);
		   //now create a new resource
		   bByte = (byte*) emalloc(sizeof(byte));
		   bByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*16);
		   bByte->size = 16;
		   bByte->pointer = 0;
		   ZEND_REGISTER_RESOURCE(return_value, bByte ,le_itextphpanotherByteResource);

		}
	    }
	    
	    
	    break;
	}
	
	
    }
}

/* {{{ proto itextphp_bytes_getBytesBasedonEncoding(string theString, string Encoding)
   return byte resource */

PHP_FUNCTION(itextphp_bytes_getBytesBasedonEncoding)
{
    zval * theString, * Encoding, *aclass;
    char *TheString = NULL;
    char *encodingScheme = NULL;
    zend_class_entry * aClass = NULL;
    int length = 0;
    iconv_t cd;
    size_t result, out_left;
    unsigned char *ndl_buf;
    byte * aByte;
    
    switch(ZEND_NUM_ARGS())
    {
	
	case 2:
	{
	    if (zend_parse_parameters(2, "ss", &theString, &Encoding) != FAILURE)
	    {
		TheString = Z_STRVAL_P(theString);
		encodingScheme = Z_STRVAL_P(Encoding);
		
		if (TheString != NULL && encodingScheme != NULL)
		{
		    cd = iconv_open(encodingScheme, "ISO-8859-1");
	
	            if (cd == (iconv_t)(-1)) {
		        RETURN_NULL()
	            }
		    
		    //compute the most appropriate length
		    length = Z_STRLEN_P(Encoding)*sizeof(int) + 15;
			
			
		    result = iconv(cd, (char **) &TheString, &length, (char **)
				&ndl_buf, &out_left);
		    
		    //convert ndl_buf to a byte resource
		    aByte = (byte*) emalloc(sizeof(byte));
		    aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*strlen(ndl_buf));
		    aByte->size = strlen(ndl_buf);
		    aByte->pointer = 0;
		    //copy the data
		    memcpy(aByte->theByte, ndl_buf,strlen(ndl_buf));
		    ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
		    
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
	    break;
	}
	case 3:
	{
	    if (zend_parse_parameters(3, "sso", &theString, &Encoding, &aclass) != FAILURE)
	    {
		aClass = zend_get_class_entry(aclass);
		TheString = Z_STRVAL_P(theString);
		encodingScheme = Z_STRVAL_P(Encoding);
		
		if (TheString != NULL && encodingScheme != NULL && aClass != NULL)
		{
		    cd = iconv_open(encodingScheme, "ISO-8859-1");
	
	            if (cd == (iconv_t)(-1)) {
			//throw exception
			zend_throw_exception(aClass, "Could not open encoding scheme", 0 TSRMLS_CC);
		        RETURN_NULL()
	            }
		    
		    //compute the most appropriate length
		    length = Z_STRLEN_P(Encoding)*sizeof(int) + 15;
			
			
		    result = iconv(cd, (char **) &TheString, &length, (char **)
				&ndl_buf, &out_left);
		    
		    //convert ndl_buf to a byte resource
		    aByte = (byte*) emalloc(sizeof(byte));
		    aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*strlen(ndl_buf));
		    aByte->size = strlen(ndl_buf);
		    aByte->pointer = 0;
		    //copy the data
		    memcpy(aByte->theByte, ndl_buf,strlen(ndl_buf));
		    //ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
		    RETURN_STRING(aByte->theByte, 1);
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

/* {{{ proto itextphp_bytes_read(byte resource)
   return php string */

PHP_FUNCTION(itextphp_bytes_read)
{
    zval *abyte, *abyte2, *theoffset, *thelength;
    byte * aByte, * aByte2;
    int theLength = 0;
    int theOffset = 0;
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 1:
	{
	    if(zend_parse_parameters(1,"r", &abyte)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		aByte->pointer++;
		char * dataChar = (char *)emalloc(sizeof(char)+1);
		
		dataChar[0] = (char)(aByte->theByte[aByte->pointer-1]);    
		
		
		
		RETURN_STRING(dataChar, 1);
		
		
	    }
	}
	case 4:
	{
	    if(zend_parse_parameters(4,"rrll", &abyte, &abyte2, &theoffset, &thelength)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		convert_to_long(theoffset);
		theOffset = Z_LVAL_P(theoffset);
		
		convert_to_long(thelength);
		theLength = Z_LVAL_P(thelength);
		
		if (aByte !=NULL && aByte2 != NULL)
		{
		    int k = 0;
		    for (k=0; k < theLength; k++)
		    {
			aByte2->theByte[theOffset] = aByte->theByte[k];
			theOffset++;
		    }
		    aByte->pointer = aByte->pointer + theLength;
		    
		    char * dataChar = (char *)emalloc(sizeof(char)+1);
		
		dataChar[0] = (char)(k);    
		
		
		
		RETURN_STRING(dataChar, 1);
		    
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



/* {{{ proto itextphp_bytes_readFully(byte resource)
   return byte resource */

PHP_FUNCTION(itextphp_bytes_readFully)
{
    
    
    zval *abyte, *abyte2, *theoffset, *thelength;
    byte * aByte, * aByte2;
    int theLength = 0;
    int theOffset = 0;
    
    
    switch(ZEND_NUM_ARGS())
    {
	case 4:
	{
	    if(zend_parse_parameters(4,"rrll", &abyte, &abyte2, &theoffset, &thelength)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &abyte2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		convert_to_long(theoffset);
		theOffset = Z_LVAL_P(theoffset);
		
		convert_to_long(thelength);
		theLength = Z_LVAL_P(thelength);
		
		if (aByte !=NULL && aByte2 != NULL)
		{
		    int k = 0;
		    for (k=0; k < theLength; k++)
		    {
			if (k >= aByte->size)
			{
			    aByte->pointer = aByte->pointer + theLength;
			    char * dataChar = (char *)emalloc(sizeof(char)+1);
			    dataChar[0] = (char)(k);    
		            RETURN_STRING(dataChar, 1);
			}
			aByte2->theByte[theOffset] = aByte->theByte[k];
			theOffset++;
		    }
		    aByte->pointer = aByte->pointer + theLength;
		    
		    char * dataChar = (char *)emalloc(sizeof(char)+1);
		
		dataChar[0] = (char)(k);    
		
		
		
		RETURN_STRING(dataChar, 1);
		    
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










/* {{{ proto itextphp_bytes_skip(byte resource, int length)
   */

PHP_FUNCTION(itextphp_bytes_skip)
{
    zval *abyte, *aposition;
    byte * aByte;
    int aPosition = 0;
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rl", &abyte, &aposition)==FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		convert_to_long(aposition);
		aPosition = Z_LVAL_P(aposition);
		aByte->pointer = aByte->pointer + aPosition;
		
		
		
	    }
	}
    }
    
}

/* {{{ proto itextphp_bytes_append(byte resource, int value...)
  return byte resource */

PHP_FUNCTION(itextphp_bytes_append)
{
    zval *param1, *param2, *param3, *param4;
    byte * aByte;
    byte * aByte2;
    int valueInt = 0;
    int valueInt2 = 0;
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 2:
	{
	    if(zend_parse_parameters(2,"rl", &param1, &param2)!=FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &param1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		convert_to_long(param2);
		valueInt = Z_LVAL_P(param2);
		
		aByte->theByte = (unsigned char *)erealloc(aByte->theByte, sizeof(unsigned char)*aByte->size + 1);
		
		aByte->theByte[aByte->pointer] = valueInt;
		aByte->pointer++;
		aByte->size = aByte->size+1;
		ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
		
	    }
	    break;
	}
	case 4:
	{
	    if(zend_parse_parameters(4,"rrll", &param1, &param2, &param3, &param4)!=FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &param1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		ZEND_FETCH_RESOURCE(aByte2, byte *, &param2, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		convert_to_long(param3);
		valueInt = Z_LVAL_P(param3);
		
		convert_to_long(param4);
		valueInt2 = Z_LVAL_P(param4);
		
		//increase the size by valueInt2
		aByte->theByte = (unsigned char *)erealloc(aByte->theByte, sizeof(unsigned char)*aByte->size + valueInt2);
		aByte->size = aByte->size + valueInt2;
		int k = 0;
		
		for (k = 0; k < valueInt2; k++)
		{
		    aByte->theByte[valueInt] = aByte2->theByte[k];
		    aByte->pointer++;
		    valueInt++;
		}
		ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
	    }
	    break;
	}
    }
}


/* {{{ proto itextphp_bytes_resetPointer(byte resource)
*/

PHP_FUNCTION(itextphp_bytes_resetPointer)
{
    
    zval *param1;
    byte * aByte;
    
    
    
    
    
    switch(ZEND_NUM_ARGS())
    {
    
        case 1:
	{
	    if(zend_parse_parameters(1,"r", &param1)==FAILURE){
	        RETURN_FALSE;
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(aByte, byte *, &param1, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		aByte->pointer=0;
	    }
	}
     }
}









