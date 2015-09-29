#include "iTextPHP.h"


/* {{{ proto int itextphp_bytes_getSize(resource bytearray)
   return the size of a byte resource */
PHP_FUNCTION(itextphp_bytes_getSize)
{
    zval * bytearray;
    byte * aByte;
    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if(zend_parse_parameters(1,"r", &bytearray)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                ZEND_FETCH_RESOURCE(aByte, byte *, &bytearray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                if (aByte== NULL)
                {
                    RETURN_LONG(0)
                }
                else
                {
                RETURN_LONG(aByte->size);
                }
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
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1, "l", &actLength) == SUCCESS)
            {
                //convert_to_long(length);
                //actLength = Z_LVAL_P(length);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*actLength);
                aByte->size = actLength;
                aByte->pointer = 0;
                ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
            }
            else if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1, "a", &theArray) == SUCCESS)
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
                for (k = 0;; zend_hash_move_forward_ex(HASH_OF(theArray), &pos))
                {
                    if (zend_hash_get_current_data_ex(HASH_OF(theArray), (void **) &item, &pos) == FAILURE)
                    {
                        break;
                    }
                    convert_to_long_ex(item);
                    int theItem = Z_LVAL_PP(item);
                    aByte->theByte[count] = theItem;
                    count++;
                }
                ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
            }
            break;
        }

        default:
        {
            WRONG_PARAM_COUNT;
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
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3, "rll", &abyte, &actPosition, &actValue) != FAILURE)
            {
                ZEND_FETCH_RESOURCE(aByte, byte *, &abyte, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                if (aByte!= NULL)
                {
                    /*convert_to_long(position);
                    actPosition = Z_LVAL_P(position);
                    convert_to_long(value);
                    actValue = Z_LVAL_P(value);*/
                    RETURN_LONG((int)(actValue & aByte->theByte[actPosition]));
                }
             }
             break;
        }
        case 2:
        {
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3, "rl", &abyte, &position) != FAILURE)
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




/* {{{ proto  itextphp_bytes_createfromRaw(string rawdata)
   return a byte resource based on rawdata */

PHP_FUNCTION(itextphp_bytes_createfromRaw)
{

    char * actRawData;
    byte * aByte;

    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1, "s", &actRawData) != FAILURE)
            {
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*strlen(actRawData));
                aByte->size = strlen(actRawData);
                aByte->pointer = 0;
                /**copy the data**/
                memcpy(aByte->theByte, actRawData,strlen(actRawData));
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

    int actData;
    byte * aByte;

    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1, "l", &actData) != FAILURE)
            {
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

/* {{{ proto  itextphp_bytes_createfromChar(char data)
   return a byte resource based on char but cast to an int */

PHP_FUNCTION(itextphp_bytes_createfromChar)
{

    char *actData;
    byte * aByte;
    int length = 0;
    

    switch(ZEND_NUM_ARGS())
    {
        case 1:
        {
            if (zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, ZEND_NUM_ARGS() TSRMLS_CC,  "s", &actData, &length) != FAILURE)
            {
	        
                
		zend_printf("length is %i", length);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char) + 1);
                aByte->size = 1;
                aByte->theByte[0] = (unsigned char)actData[0];
                ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
            }
            break;
        }
    }
}
