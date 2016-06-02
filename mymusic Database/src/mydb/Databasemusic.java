package mydb;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.sql.*;
import java.util.*;
import org.json.*;
import javax.json.*;
import java.lang.*;

import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;
import org.json.JSONException;
public class Databasemusic {

	// JDBC driver name and database URL
	   static final String JDBC_DRIVER = "com.mysql.jdbc.Driver";  
	   static final String DB_URL = "jdbc:mysql://localhost/";
	   
	   public static String DBName = "musicdb";
		
		static final String username = "root";
		static final String password = "ukoolz";
		
		
		public static final String Path ="/home/umesh/Desktop/Music";
		  
		
		
		
		
		static Connection conn = null;
		

	public static void main(String[] args) {
		
		// TODO Auto-generated method stub
		
		//createDataBase(DBName,username,password);
		//createTables(DBName,username,password);
		//createConnection();
		//LoadArtistsTable();
		//LoadTracksTable();
		LoadGroupTable();
		try {
			LoadTracks();
			LoadSimilarTable();
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	static public void createDataBase( String DBName , String USER , String PASS )
	{  
		Connection conn = null;
		Statement statmnt = null;
	   try{
	      //STEP 2: Register JDBC driver
	      Class.forName("com.mysql.jdbc.Driver");

	      //STEP 3: Open a connection
	      System.out.println("Connecting to database...");
	      conn = DriverManager.getConnection(DB_URL, USER, PASS);

	      //STEP 4: Execute a query
	      System.out.println("Creating database...");
	      statmnt = conn.createStatement();
	      
	      String sql = "CREATE DATABASE "+DBName;
	      statmnt.executeUpdate(sql);
	      System.out.println("Database created successfully...");
	   }catch(SQLException se){
	      //Handle errors for JDBC
		   
		  //System.out.println("SQL Error code :: "+se.getErrorCode());
		  
		  if(se.getErrorCode() != 1007 )
	      se.printStackTrace();
	   }catch(Exception e){
	      //Handle errors for Class.forName
	      e.printStackTrace();
	   }finally{
	      //finally block used to close resources
	      try{
		         if(statmnt!=null)
		            statmnt.close();
	      }catch(SQLException se2){
	      }// nothing we can do
	      try{
	         if(conn!=null)
	            conn.close();
	      }catch(SQLException se){
	         se.printStackTrace();
	      }//end finally try
	   }//end try
	   System.out.println("Goodbye!");

	}//end function createDatabase
	
	static public void createTables( String DBName , String USER , String PASS )
	{
			Connection conn = null;
		    Statement statmnt = null;
		   try{
		      //STEP 2: Register JDBC driver
		      Class.forName("com.mysql.jdbc.Driver");

		      //STEP 3: Open a connection
		      System.out.println("Connecting to a selected database...");
		      conn = DriverManager.getConnection(DB_URL+DBName, USER, PASS);
		      System.out.println("Connected database successfully...");
		      
		      //STEP 4: Execute a query
		      System.out.println("Creating table in given database...");
		      statmnt = conn.createStatement();
		      /*String sql = "use 14CS60R31;"; 
		      statmnt.executeUpdate(sql);*/
		      
		      String sql = "CREATE TABLE artist " +
		                   "(artistId VARCHAR(255) not NULL, " +
		                   " artistName VARCHAR(255) not NULL, " + 
		                   " PRIMARY KEY ( ArtistId ))ENGINE=InnoDB"; 

		      statmnt.executeUpdate(sql);
		      System.out.println("Created table 'Artists' in given database...");
		      
		      
		      sql = "CREATE TABLE track " +
	                   "(TrackId VARCHAR(255) not NULL, " +
	                   " ArtistName VARCHAR(128) REFERENCES Artists( ArtistName ), " +
	                   " Title VARCHAR(255), " + 
	                   " Year YEAR, " + 
	                   " PRIMARY KEY ( TrackId ) "+
	                   //" FOREIGN KEY fk_1(ArtistName) REFERENCES Artists( ArtistName )"+
	                   ")ENGINE=InnoDB"; 

	      statmnt.executeUpdate(sql);
	      System.out.println("Created table 'Tracks' in given database...");
	      
	      sql = "CREATE TABLE groups " +
                  "(GroupId VARCHAR(128) not NULL, " +
                  " TrackId VARCHAR(255) not NULL REFERENCES Tracks( TrackId ), " + 
                  " PRIMARY KEY ( GroupId , TrackId )"+
	              //" FOREIGN KEY (TrackId) REFERENCES Tracks( TrackId )"+
	              ")ENGINE=InnoDB"; ; 

     statmnt.executeUpdate(sql);
     System.out.println("Created table 'GroupTracks' in given database...");
     
     sql = "CREATE TABLE tags " +
             "(trackId VARCHAR(255) not NULL REFERENCES Tracks( TrackId ), " +
             " tagName VARCHAR(255) not NULL, " + 
             " tagCount VARCHAR(255), " +
             " PRIMARY KEY ( TrackId , TagName )"+
	                //   " FOREIGN KEY (TrackId) REFERENCES Tracks( TrackId )"+
	                   ")ENGINE=InnoDB"; ; 

     statmnt.executeUpdate(sql);
     System.out.println("Created table 'TracksTagCount' in given database...");
		      
     
     sql = "CREATE TABLE duplicates " +
             "(origTrackId VARCHAR(255) not NULL REFERENCES Tracks( TrackId ), " +
             " trackId VARCHAR(255) not NULL, " + 
             " similarity DOUBLE, " +
             " PRIMARY KEY ( PrimTrackId , TrackId )"+
	                  // " FOREIGN KEY (PrimTrackId) REFERENCES Tracks( TrackId )"+
	                   ")ENGINE=InnoDB"; ; 

     statmnt.executeUpdate(sql);
     System.out.println("Created table 'SimalarTracks' in given database...");

		   }catch(SQLException se){
		      //Handle errors for JDBC
			   
			   if(se.getErrorCode() != 1050 )
		      se.printStackTrace();
		   }catch(Exception e){
		      //Handle errors for Class.forName
		      e.printStackTrace();
		   }finally{
			 //finally block used to close resources
			      try{
			         if(statmnt!=null)
			            conn.close();
			      }catch(SQLException se){
			      }// do nothing
			      try{
			         if(conn!=null)
			            conn.close();
			      }catch(SQLException se){
			         se.printStackTrace();
			      }//end finally try
			   }//end try
			   System.out.println("Goodbye!");
	
		
	}//end createTables()
	
	static public void LoadArtistsTable()
	{
		String [] tokens;
		String artistName;
		Statement statmnt = null;
		String totalquery="insert ignore into artist values";
		int count=0;
		int i;
		
		statmnt = createConnection();
		
		try(BufferedReader buffread = new BufferedReader(new FileReader(Path+"/unique_artists.txt"))) {
	        StringBuilder sb = new StringBuilder();
	        String line = buffread.readLine();

	        while (line != null) {
	            sb.append(line);
	            sb.append(System.lineSeparator());
	            line = buffread.readLine();
	            
	            if( line != null )
	            {
			            	tokens = line.split("<SEP>");
			            	artistName = tokens[3];
			            	
			            	if(tokens[3].contains("'"))
			            	{
			            		artistName = tokens[3].replaceAll("'", "*");
			    
			            	}
			            	if(count<10000){
				            	totalquery=totalquery.concat("('"+tokens[0]+"','"+artistName+"'),");
				            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
				            	count++;
				            	}
				            	else
				            	{
				            		totalquery=totalquery.concat("('"+tokens[0]+"','"+artistName+"');");
				            		count=0;
				            		System.out.println(totalquery);
				            		statmnt.executeUpdate(totalquery);
				            		totalquery=null;
				            		totalquery="insert ignore into artist values";
				            	}
			            
			            	//statmnt.executeUpdate("insert ignore into Artists values('"+tokens[0]+"','"+artistName+"');");
	            }
	            
	        }
	        buffread.close();
	        
	    } catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		finally{
		      //finally block used to close resources
		      try{
			         if(statmnt!=null)
			            statmnt.close();
		      }catch(SQLException se2){
		      }// nothing we can do
		      try{
		         if(conn!=null)
		            conn.close();
		      }catch(SQLException se){
		         se.printStackTrace();
		      }}//end finally try
		
		System.out.println("\n***Artist Table Loaded");
	}//end function LoadArtistsTable
	
	
	static public void LoadTracksTable()
	{
		String [] tokens;
		String artistName;
		String title;
		Statement statmnt = null;
		
		String totalquery="insert ignore into track values";
		int count=0;
		int i;
		
		statmnt = createConnection();
		
		try(BufferedReader buffread = new BufferedReader(new FileReader(Path+"/tracks_per_year.txt"))) {
	        StringBuilder sb = new StringBuilder();
	        String line = buffread.readLine();

	        while (line != null) {
	            sb.append(line);
	            sb.append(System.lineSeparator());
	            line = buffread.readLine();
	            
	            if( line != null )
	            {
			            	tokens = line.split("<SEP>");
			            	artistName = tokens[2];
			            	
			            	if(tokens[2].contains("'"))
			            	{
			            		artistName = tokens[2].replaceAll("'", "-");
			    
			            	}
			            	
			            	title = tokens[3];
			            	
			            	if(tokens[3].contains("'"))
			            	{
			            		title = tokens[3].replaceAll("'", "-");
			    
			            	}
			            	if(count<10000){
				            	totalquery=totalquery.concat("('"+tokens[1]+"','"+artistName+"','"+title+"','"+tokens[0]+"'),");
				            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
				            	count++;
				            	}
				            	else
				            	{
				            		totalquery=totalquery.concat("('"+tokens[1]+"','"+artistName+"','"+title+"','"+tokens[0]+"');");
				            		count=0;
				            		System.out.println(totalquery);
				            		statmnt.executeUpdate(totalquery);
				            		totalquery=null;
				            		totalquery="insert ignore into track values";
				            	}
			            
			            	
			            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
			            
			            //	statmnt.executeUpdate("insert ignore into Tracks values('"+tokens[1]+"','"+artistName+"','"+title+"','"+tokens[0]+"');");
			            	
			            	
			            
	            }
	            
	        }
	        
	        buffread.close();
	        
	        
	       try(BufferedReader br1 = new BufferedReader(new FileReader(Path+"/unique_tracks.txt"))) {
		      // StringBuilder sb = new StringBuilder();
		      //  String line = br1.readLine();

		        while (line != null) {
		            sb.append(line);
		            sb.append(System.lineSeparator());
		            line = br1.readLine();
		            
		            if( line != null )
		            {
				            	tokens = line.split("<SEP>");
				            	artistName = tokens[2];
				            	
				            	if(tokens[2].contains("'"))
				            	{
				            		artistName = tokens[2].replaceAll("'", "-");
				    
				            	}
				            	

				            	title = tokens[3];
				            	
				            	if(tokens[3].contains("'"))
				            	{
				            		title = tokens[3].replaceAll("'", "-");
				    
				            	}
				            	if(count<10000){
					            	totalquery=totalquery.concat("('"+tokens[0]+"','"+artistName+"','"+title+"',null),");
					            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
					            	count++;
					            	}
					            	else
					            	{
					            		totalquery=totalquery.concat("('"+tokens[0]+"','"+artistName+"','"+title+"',null);");
					            		count=0;
					            		System.out.println(totalquery);
					            		statmnt.executeUpdate(totalquery);
					            		totalquery=null;
					            		totalquery="insert ignore into track values";
					            	}
				            
				            	//statmnt.executeUpdate("insert ignore into Tracks values('"+tokens[0]+"','"+artistName+"','"+title+"',null);");
				            	
				            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
				            
		            }
		            
		        }
		        
		        br1.close();
	        }
		}
	        
	     catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} /*catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}*/
		catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		finally{
		      //finally block used to close resources
		      try{
			         if(statmnt!=null)
			            statmnt.close();
		      }catch(SQLException se2){
		      }// nothing we can do
		      try{
		         if(conn!=null)
		            conn.close();
		      }catch(SQLException se){
		         se.printStackTrace();
		      }}//end finally try
		
		
		System.out.println("\n***Tracks Table Loaded");
		
	}//end LoadTracksTable()
	
	static public void LoadGroupTable()
	{
		String [] token;
		String [] Groupid;
		Statement statmnt = null;
		int count=0;
		String totalquery="insert ignore into groups values";
		int i;
		
		statmnt = createConnection();
		try(BufferedReader buffread = new BufferedReader(new FileReader(Path+"/shs_dataset_train.txt"))) {
	        StringBuilder sbs = new StringBuilder();
	        String lineread = buffread.readLine();

	        while (lineread != null ) {
	            sbs.append(lineread);
	            sbs.append(System.lineSeparator());
	            lineread = buffread.readLine(); 
	        
	            
	            if( lineread.contains("%") && lineread!= null) 
	            {
			            	token = lineread.split(",");
			            	//String Name = token[0];
			            	//System.out.println(artistName);
			            	//Groupid=Name.split(" ");
			            	//String gid=Groupid[0].replace("%","");
			            	String groupid = token[0].replace("%", "");
			            	if(lineread.contains("%-"))
			            	{groupid = token[0].replace("%-", "");}
			            		
			            	
			            	System.out.println(groupid);
			            
			            	lineread=buffread.readLine();
			            	while(lineread.contains("<SEP>") && lineread != null)
			            	{
			            	token=lineread.split("<SEP>");
			            	
			            	//token = lineread.split("<SEP>");
			            	//System.out.println("-----------");
			            	lineread=buffread.readLine();
			            	//System.out.println(token[0]);
			            	if(count<100){
				            	totalquery=totalquery.concat("('"+groupid+"','"+token[0]+"'),");
				            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
				            	count++;
				            	}
				            	else
				            	{
				            		totalquery=totalquery.concat("('"+groupid+"','"+token[0]+"');");
				            		count=0;
				            		System.out.println(totalquery);
				            		statmnt.executeUpdate(totalquery);
				            		totalquery=null;
				            		totalquery="insert ignore into groups values";
				            	}
			            	
			            	//statmnt.executeUpdate("insert ignore into GroupTracks values('"+groupid+"','"+token[0]+"');");
			            	}
			            
			         //   	statmnt.executeUpdate("insert ignore into Artists values('"+tokens[0]+"','"+artistName+"');");
			            	
			            
	            }
	            
	        }
	        buffread.close();
	        
	    } catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}catch (NullPointerException e) {
			// TODO Auto-generated catch block
			//e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		finally{
		      //finally block used to close resources
		      try{
			         if(statmnt!=null)
			            statmnt.close();
		      }catch(SQLException se2){
		      }// nothing we can do
		      try{
		         if(conn!=null)
		            conn.close();
		      }catch(SQLException se){
		         se.printStackTrace();
		      }}//end finally try
		
		System.out.println("\n***Group Table Loaded");
	}//end function Load Group Table
	
	static public void LoadTracks() throws Exception
	{
		String [] token;
		String [] Groupid;
		String [] tok;
		File dir;
		String tokenj;
		String[] tNcount;
		File[] fileList;
		String trackid;
		String[] tagsname =  new String[250];
		Statement statmnt = null;
		 String tagName , tempo;
		 int count=0;
		String totalquery="insert ignore into tags values";
		 int tagcounter;
		 JsonArray array = Json.createArrayBuilder().build();
		String entirefilepath=Path.concat("/lastfm_test/");
		int i;
		int c;
		statmnt = createConnection();
		try{
		for(char alpha = 'H'; alpha <= 'Z';alpha++) {
			for(char alpha1 = 'A'; alpha1 <= 'Z';alpha1++) {
				for(char alpha2 = 'A'; alpha2 <= 'Z';alpha2++) {
		    System.out.println(entirefilepath+alpha+"/"+alpha1+"/"+alpha2);
		    
		    	dir = new File(entirefilepath+alpha+"/"+alpha1+"/"+alpha2);
		    	fileList=dir.listFiles();
		    	for (File file : fileList){
					if (file.isFile())
					{
						BufferedReader buffer = new BufferedReader(new FileReader(entirefilepath+"/"+alpha+"/"+alpha1+"/"+alpha2+"/"+file.getName()));
						StringBuilder sbs = new StringBuilder();
				        String lineread = buffer.readLine();
				        while (lineread != null )
				        {
				            sbs.append(lineread);
				            sbs.append(System.lineSeparator());
				            token=lineread.split("tags");
				            trackid = file.getName().replace(".json", "");
				            System.out.println(trackid);
				            FileReader reader = new FileReader(entirefilepath+"/"+alpha+"/"+alpha1+"/"+alpha2+"/"+file.getName());
				            JSONParser jsonParser = new JSONParser();
							JSONObject jsonObject = (JSONObject) jsonParser.parse(reader);
							JSONArray lang= (JSONArray) jsonObject.get("tags");
							c=0;
							//String firstName =  (String) jsonObject.get("tags");
							//System.out.println("The first name is: " + firstName);
							 for(int j=0; j<lang.size(); j++){
								               //System.out.println("The " + j + " element of the array: "+lang.get(j));
								              
								 	            }
								 	            Iterator j = lang.iterator();
								 	          String s = lang.toString();
								 	         StringTokenizer multi = new StringTokenizer(s, "[]");
								 	        while (multi.hasMoreTokens())
					            			{
					            			    tokenj = multi.nextToken();
					            			    
					            			   // System.out.println(">>>>>>>>>>>>>>>"+tokenj);
					            			    if(tokenj != null && tokenj.contains(",") )
					            			    	{
					            			    	tNcount = tokenj.split(",");
					            			    	
					            			    	//System.out.println(">>>>>>>>>>>>>>>"+tNcount[0]);
					            			    	
					            			    	for(int i1 =0 ; i1<tNcount.length ; i1++ )
					            			    		{
						            			    		
						            			    			
						            			    			tagsname[c] = tNcount[i1];
						            			    			
						            			    			System.out.println(i1+"Tag:"+tagsname[c]);
						            			    			c++;
						            			    		
					            			    		}
					            			    		
					            			    	}
					            			}
								 	       for(i = 0 ; i < (c-2) ; i = i+2 )
					            			{
					            				tagName = tagsname[i].replace("'", "*");
					            				tempo = tagsname[i+1].trim();
					            				//System.out.println("integer value|"+tempo);
					            				//System.out.println("length"+c+" i"+i);
					            				//tagcounter = Integer.parseInt(tempo);
					            				//System.out.println(tagcounter);
					            				if(count<5000){
									            	totalquery=totalquery.concat("('"+trackid+"','"+tagName+"','"+tempo+"'),");
									            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
									            	count++;
									            	}
									            	else
									            	{
									            		totalquery=totalquery.concat("('"+trackid+"','"+tagName+"','"+tempo+"');");
									            		count=0;
									            		statmnt.executeUpdate(totalquery);
									            		totalquery=null;
									            		totalquery="insert ignore into tags values";
									            	}
					            				//statmnt.executeUpdate("insert ignore into TracksTagCount values('"+trackid+"','"+tagName+"',"+tempo+");");
					            			}

								 	            JSONArray temp = (JSONArray) jsonObject.get("tags");
								                Iterator<String> iterator = temp.iterator();
								               // System.out.println("The " + j + "----------------: "+iterator);
								                int length = temp.size();
								                if (length > 0)
								                {
								                    String [] recipients = new String [length];
								                    for (int k = 0; k < length; k++) 
								                    {
								                        recipients[k] = temp.toJSONString();
								                       // System.out.println("The " + k + " element of the array: "+temp.toString()+recipients[k]);
								                        
								                    }}
								 	            // take each value from the json array separately
								 	           

				           
				            lineread = buffer.readLine();
				            }
				        
						}
					}
		    	}
			}
		
			}
			
	        
	
	 } catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ParseException ex) {
			            ex.printStackTrace();
				        } catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}catch (NumberFormatException e) {
		      //Will Throw exception!
		      //do something! anything to handle the exception.
		}catch (NullPointerException e) {
			// TODO Auto-generated catch block
			//e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		      //finally block used to close resources
		      try{
			         if(statmnt!=null)
			            statmnt.close();
		      }catch(SQLException se2){
		      }// nothing we can do
		      try{
		         if(conn!=null)
		            conn.close();
		      }catch(SQLException se){
		         se.printStackTrace();
		      }//end finally try
		
		System.out.println("\n***Group Table Loaded");
			
	}//end function Load Group Table
	
	static public void LoadSimilarTable() throws Exception
	{
		String [] token;
		String [] Groupid;
		String [] tok;
		File dir;
		String tokenj;
		String[] tNcount;
		File[] fileList;
		String trackid;
		 int count=0;
			String totalquery="insert ignore into duplicates values";
		String[] tagsname =  new String[250];
		Statement statmnt = null;
		 String tagName , tempo;
		 int tagcounter;
		 JsonArray array = Json.createArrayBuilder().build();
		String entirefilepath=Path.concat("/lastfm_test/");
		int i;
		int c;
		Double sim;
		statmnt = createConnection();
		try{
		for(char alpha = 'C'; alpha <= 'Z';alpha++) {
			for(char alpha1 = 'A'; alpha1 <= 'Z';alpha1++) {
				for(char alpha2 = 'A'; alpha2 <= 'Z';alpha2++) {
		    System.out.println(entirefilepath+alpha+"/"+alpha1+"/"+alpha2);
		    
		    	dir = new File(entirefilepath+alpha+"/"+alpha1+"/"+alpha2);
		    	fileList=dir.listFiles();
		    	for (File file : fileList){
					if (file.isFile())
					{
						BufferedReader buffer = new BufferedReader(new FileReader(entirefilepath+"/"+alpha+"/"+alpha1+"/"+alpha2+"/"+file.getName()));
						StringBuilder sbs = new StringBuilder();
				        String lineread = buffer.readLine();
				        while (lineread != null )
				        {
				            sbs.append(lineread);
				            sbs.append(System.lineSeparator());
				            token=lineread.split("tags");
				            trackid = file.getName().replace(".json", "");
				            System.out.println(trackid);
				            FileReader reader = new FileReader(entirefilepath+"/"+alpha+"/"+alpha1+"/"+alpha2+"/"+file.getName());
				            JSONParser jsonParser = new JSONParser();
							JSONObject jsonObject = (JSONObject) jsonParser.parse(reader);
							JSONArray lang= (JSONArray) jsonObject.get("similars");
							c=0;
							//String firstName =  (String) jsonObject.get("tags");
							//System.out.println("The first name is: " + firstName);
							 for(int j=0; j<lang.size(); j++){
								              // System.out.println("The " + j + " element of the array: "+lang.get(j));
								              
								 	            }
								 	            Iterator j = lang.iterator();
								 	          String s = lang.toString();
								 	         StringTokenizer multi = new StringTokenizer(s, "[]");
								 	        //System.out.println(">>>>>>>>>>>>>>----------------->"+s);
								 	        while (multi.hasMoreTokens())
					            			{
					            			    tokenj = multi.nextToken();
					            			    
					            			   System.out.println(">>>>>>>>>>>>>>>"+tokenj);
					            			    if(tokenj != null && tokenj.contains("T") )
					            			    	{
					            			    	tNcount = tokenj.split(",");
					            			    	tagName = tNcount[0];
					            			    	sim = Double.parseDouble(tNcount[1]);
					            			    	if(count<10000){
										            	totalquery=totalquery.concat("('"+trackid+"','"+tagName+"','"+sim+"'),");
										            	//System.out.println(tokens[1]+" "+artistName+" "+title+" "+tokens[0]);
										            	count++;
										            	}
										            	else
										            	{
										            		totalquery=totalquery.concat("('"+trackid+"','"+tagName+"','"+sim+"');");
										            		count=0;
										            		statmnt.executeUpdate(totalquery);
										            		totalquery=null;
										            		totalquery="insert ignore into duplicates values";
										            	}
					            			    	//statmnt.executeUpdate("insert ignore into SimalarTracks values('"+trackid+"','"+tagName+"','"+sim+"');");
					            			    	//System.out.println("The " + sim + " element of the array: "+tagName);
					            			    	}
					            			}
								 	     
								 	            // take each value from the json array separately
								 	           

				           
				            lineread = buffer.readLine();
				            }
				        
						}
					}
		    	}
			}
		
			}
			
	        
	
	 } catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ParseException ex) {
			            ex.printStackTrace();
				        } catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}catch (NumberFormatException e) {
		      //Will Throw exception!
		      //do something! anything to handle the exception.
		}catch (NullPointerException e) {
			// TODO Auto-generated catch block
			//e.printStackTrace();
		} /*catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}*/
		      //finally block used to close resources
		      try{
			         if(statmnt!=null)
			            statmnt.close();
		      }catch(SQLException se2){
		      }// nothing we can do
		      try{
		         if(conn!=null)
		            conn.close();
		      }catch(SQLException se){
		         se.printStackTrace();
		      }//end finally try
		
		System.out.println("\n***similar Table Loaded");
			
	}//end function Load similar Table
	

	static Statement createConnection()
	{
		Statement statmnt = null;
		
	   try{
	      //STEP 2: Register JDBC driver
	      Class.forName("com.mysql.jdbc.Driver");

	      //STEP 3: Open a connection
	      System.out.println("Connecting to a selected database...");
	      conn = DriverManager.getConnection( DB_URL+DBName , username , password );
	      
	      statmnt = conn.createStatement();
	   	}catch(SQLException se){
		      //Handle errors for JDBC
			   
			   if(se.getErrorCode() != 1050 )
		      se.printStackTrace();
		   } catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	   
	   return statmnt;
	}// end createConnection



}
