/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Data for the table `comment` */

LOCK TABLES `comment` WRITE;

insert  into `comment`(`id_comment`,`id_topic`,`id_user`,`text`,`timestamp_add`,`is_deleted`) values (1,1,2,'Is this Gizmodo Australia?','2013-05-12 18:56:46',0);
insert  into `comment`(`id_comment`,`id_topic`,`id_user`,`text`,`timestamp_add`,`is_deleted`) values (2,3,1,'NASA, Roscosmos, SpaceX, SOMEBODY, take me to the ISS.','2013-05-12 18:57:02',0);
insert  into `comment`(`id_comment`,`id_topic`,`id_user`,`text`,`timestamp_add`,`is_deleted`) values (3,3,2,'Bucket list: Play an acoustic guitar in space.','2013-05-12 18:57:08',0);

UNLOCK TABLES;

/*Data for the table `topic` */

LOCK TABLES `topic` WRITE;

insert  into `topic`(`id_topic`,`id_user`,`title`,`text`,`timestamp_add`,`is_deleted`) values (1,1,'Barns Are Red Because Of How Stars Explode','We all know that barns are usually red. But why? Well, the answer is a little more complicated than you might think, but basically it’s because of nuclear fusion.\r\n\r\nGoogler Yonatan Zunger took the time to explain the whole thing in great detail on Google+, and the train of thought goes a little something like this:\r\n\r\n- Barns are red because red paint is the cheapest and easiest to make.\r\n- Red paint is the cheapest and easiest to make because the ground is loaded with an iron-oxide compound called red orche. (or basically, rust)\r\n- The ground is loaded with red ochre because when stars die, physics dictates they generate a bunch of iron and explode.\r\n\r\nIt’s that step where things get a little more complicated.','2013-05-12 18:54:39',0);
insert  into `topic`(`id_topic`,`id_user`,`title`,`text`,`timestamp_add`,`is_deleted`) values (2,2,'The Photos From Today’s Emergency Spacewalk Are Totally Awe-Inspiring','When astronauts Chris Cassidy and Tom Marshburn left the ISS today to go fix a leaky ammonia pump all quick-like, everyone’s favourite YouTubing Canadian Commander, Chris Hadfield, stayed inside to keep things going there. But, as he is wont to do, he took some seriously awesome shots of the spacewalkers at work.\r\n\r\nAbove is shot of the pair climbing out of the airlock to embark on their little five hour plus trip into the dark vacuum of space, and the contrast of this amazing triumph of mankind against the blackness of the void is as stunning as ever.\r\n\r\nHadfield also tweeted a little image of the duo hanging out amongst all the pipes, cables and whatnot that covers the space station’s side, illustrating exactly how complicated the whole mess is. It’s a wonder that we have a structure that can support life up there, much less astronauts they can get outside and fix their orbital fortress at a mere days notice. Good thing they’ve got an awesome documentarian on board to capture those memories.','2013-05-12 18:55:19',0);
insert  into `topic`(`id_topic`,`id_user`,`title`,`text`,`timestamp_add`,`is_deleted`) values (3,3,'What Space Does To Each Of Your Five Senses','Commander of the International Space Station and awesome-stronaut Chris Hadfield just finished a weeklong series called “Senses in Space”, where he describes what spending a long time in space does to each of your perceptions. And, apparently, space is generally pretty bad for you.\r\n\r\nIn the following five videos, Hadfield talks about blurred vision, loss of taste and other unpleasant effects of the low-gravity environment — but all with his characteristic good cheer.','2013-05-12 18:56:07',0);

UNLOCK TABLES;


/*Data for the table `user` */

LOCK TABLES `user` WRITE;

insert  into `user`(`id_user`,`name`,`email`,`timestamp_add`,`is_deleted`) values (1,'Mr. First','first@email.com','2013-05-12 18:52:14',0);
insert  into `user`(`id_user`,`name`,`email`,`timestamp_add`,`is_deleted`) values (2,'Mr. Second','second@email.com','2013-05-12 18:52:41',0);
insert  into `user`(`id_user`,`name`,`email`,`timestamp_add`,`is_deleted`) values (3,'Mr. Third','third@email.com','2013-05-12 18:53:00',0);

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;