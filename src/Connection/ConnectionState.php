<?php

namespace Crab\Engine\Connection;

enum ConnectionState: string
{
    case Established = 'established';
    case Closing = 'closing';
    case Closed = 'closed';
}
