<?php

namespace Mateodioev\Request;

enum Methods
{
    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    case OPTIONS;
    case HEAD;
}
