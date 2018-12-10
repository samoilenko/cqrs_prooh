#!/bin/bash

#if [ ! -f /project/package.json ];
if [ ! -d /project/node_modules ];
then
    tail -f /dev/null && /bin/bash
else
    grunt serve
fi