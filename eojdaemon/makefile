CC=gcc
CFLAGS=-std=gnu99 -c -I/usr/include/mysql -I/usr/include/libxml2
LDFLAGS=-lpthread -lxml2 -lmysqlclient -L/usr/lib64/mysql
SOURCES=$(wildcard *.c)
OBJS:=$(patsubst %.c,%.o,$(SOURCES))

eojdaemon:$(OBJS)
	$(CC) $(OBJS) $(LDFLAGS) -o $@.out

-include $(SOURCES:.c=.d)

%.d:%.c
	$(CC) -MM $(CFLAGS) $< >$@.$$$$;\
	sed 's,\($*\).o[ :]*,\1.o $@:,g' < $@.$$$$ >$@;\
	rm -f $@.$$$$

.PHONY:clean
clean:
	rm -f *.o *.d *.out
