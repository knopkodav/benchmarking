require 'rubygems'
require 'mongo'
require 'benchmark'
require 'mysql'

include Mongo

# benchmark cycle
def cycle x
  x.times{ |i|
    doc = {
      "text" => "i am any text",
      "count" => i,
      "coords" => {"x" => 100, "y" => 200, "z" => i}
    }
    yield doc
  }
end

def flush_mysql mysql
  mysql.query("DROP TABLE IF EXISTS `insert_bm`")
  mysql.query("DROP TABLE IF EXISTS `insert_bm_coords`")
  mysql.query("CREATE TABLE `insert_bm_coords`(
    `id` int unsigned not null auto_increment,
    `x` int not null,
    `y` int not null,
    PRIMARY KEY (`id`)
  )")
  mysql.query("CREATE TABLE `insert_bm`(
    `id` int unsigned not null auto_increment,
    `text` varchar(255) not null,
    `count` int not null,
    `coords_id` int unsigned not null,
    PRIMARY KEY (`id`)
  )")
  # empty init entries
  mysql.query("INSERT insert_bm_coords(x,y) VALUES(0, 0)")
  mysql.query("INSERT insert_bm(text, count, coords_id) VALUES('', 0, #{mysql.insert_id()})")
end

def flush_mongo mongo_cnn
  mongo_cnn.collection("bmdb").drop
  mongo = mongo_cnn.collection("bmdb")
  mongo.insert({"stub" => "i am empty init doc"})
  mongo
end

mysql = Mysql.new('localhost', 'testuser', 'testpass', 'test')
mongo_cnn = Connection.new.db("ruby_db")

# do benchmarking
Benchmark.bm {|x|
  points = [1_000, 10_000, 100_000, 500_000, 1_000_000]

  points.each{|c|
    flush_mysql mysql
    mongo = flush_mongo(mongo_cnn)
    
    x.report("mysql #{c.to_s} :") {
      cycle(c){|doc|
        mysql.query("INSERT insert_bm_coords(x,y) VALUES(#{doc["coords"]["x"]}, #{doc["coords"]["y"]})")
        mysql.query("INSERT insert_bm(text, count, coords_id) VALUES('#{doc["text"]}', #{doc["count"]}, #{mysql.insert_id()})")
      }
    }

    x.report("mongo #{c.to_s} :") {
      cycle(c){|doc|
        mongo.insert(doc)
      }
    }
  }
}
